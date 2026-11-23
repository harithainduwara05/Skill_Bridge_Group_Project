<?php

class CompanyManager
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function columnExists($tableName, $columnName)
    {
        $stmt = $this->conn->prepare(
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ss', $tableName, $columnName);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result && $result->num_rows > 0;
    }

    private function tableExists($tableName)
    {
        $stmt = $this->conn->prepare(
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $tableName);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result && $result->num_rows > 0;
    }

    private function internshipUsesNormalizedSchema()
    {
        return $this->columnExists('internships', 'company_email')
            && $this->columnExists('internships', 'industry_id');
    }

    private function resolveCompanyEmailByName($companyName)
    {
        $companyName = trim((string) $companyName);
        if ($companyName === '') {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT Email
             FROM company
             WHERE TRIM(Name) = TRIM(?)
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $companyName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['Email'] ?? null;
    }

    private function upsertIndustryId($industryName)
    {
        $industryName = trim((string) $industryName);
        if ($industryName === '') {
            return null;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO internship_industries (industry_name)
             VALUES (?)
             ON DUPLICATE KEY UPDATE industry_name = VALUES(industry_name)"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $industryName);
        $stmt->execute();

        $lookup = $this->conn->prepare(
            "SELECT industry_id
             FROM internship_industries
             WHERE TRIM(industry_name) = TRIM(?)
             LIMIT 1"
        );
        if (!$lookup) {
            return null;
        }

        $lookup->bind_param('s', $industryName);
        $lookup->execute();
        $row = $lookup->get_result()->fetch_assoc();
        return $row['industry_id'] ?? null;
    }

    private function parseDurationParts($duration)
    {
        $duration = trim((string) $duration);
        if ($duration === '') {
            return [null, null];
        }

        if (preg_match('/^(\d+)\s*(day|days|week|weeks|month|months|year|years)$/i', $duration, $matches)) {
            $value = (int) $matches[1];
            $unit = strtolower($matches[2]);
            return [$value, rtrim($unit, 's')];
        }

        if (preg_match('/^(\d+)$/', $duration, $matches)) {
            return [(int) $matches[1], null];
        }

        $parts = preg_split('/\s+/', $duration, 2);
        $value = isset($parts[0]) && preg_match('/^\d+$/', $parts[0]) ? (int) $parts[0] : null;
        $unit = isset($parts[1]) ? preg_replace('/[^a-z]/i', '', strtolower($parts[1])) : null;
        $unit = $unit !== null && $unit !== '' ? rtrim($unit, 's') : null;
        return [$value, $unit];
    }

    private function parseDeadlineDate($deadline)
    {
        $deadline = trim((string) $deadline);
        if ($deadline === '') {
            return null;
        }

        $timestamp = strtotime($deadline);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function persistInternshipTechTags($internshipId, $techTags)
    {
        if (!$this->tableExists('internship_tech_tag_map')) {
            return;
        }

        $tags = [];
        foreach (explode(',', (string) $techTags) as $tag) {
            $tag = trim(preg_replace('/\s+/', ' ', (string) $tag));
            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        $tags = array_values(array_unique($tags));
        if ($internshipId <= 0 || $tags === []) {
            return;
        }

        $this->conn->query("DELETE FROM internship_tech_tag_map WHERE internship_id = " . (int) $internshipId);

        foreach ($tags as $tag) {
            $tagInsert = $this->conn->prepare(
                "INSERT INTO internship_tech_tags (tag_name) VALUES (?)
                 ON DUPLICATE KEY UPDATE tag_name = VALUES(tag_name)"
            );
            if (!$tagInsert) {
                continue;
            }
            $tagInsert->bind_param('s', $tag);
            $tagInsert->execute();

            $tagLookup = $this->conn->prepare(
                "SELECT tag_id
                 FROM internship_tech_tags
                 WHERE TRIM(tag_name) = TRIM(?)
                 LIMIT 1"
            );
            if (!$tagLookup) {
                continue;
            }
            $tagLookup->bind_param('s', $tag);
            $tagLookup->execute();
            $tagRow = $tagLookup->get_result()->fetch_assoc();
            if (!$tagRow || !isset($tagRow['tag_id'])) {
                continue;
            }

            $mapStmt = $this->conn->prepare(
                "INSERT IGNORE INTO internship_tech_tag_map (internship_id, tag_id)
                 VALUES (?, ?)"
            );
            if ($mapStmt) {
                $mapStmt->bind_param('ii', $internshipId, $tagRow['tag_id']);
                $mapStmt->execute();
            }
        }
    }

    private function buildInternshipCompanyCondition($tableAlias = 'i', $companyTableAlias = 'c')
    {
        if ($this->internshipUsesNormalizedSchema()) {
            return "TRIM(COALESCE({$companyTableAlias}.Name, '')) = TRIM(?)";
        }

        return "TRIM({$tableAlias}.company) = TRIM(?)";
    }

    private function buildInternshipCompanyJoin($tableAlias = 'i', $companyTableAlias = 'c')
    {
        if ($this->internshipUsesNormalizedSchema()) {
            return "LEFT JOIN company {$companyTableAlias} ON {$companyTableAlias}.Email = {$tableAlias}.company_email";
        }

        return '';
    }

    private function buildInternshipIndustryJoin($tableAlias = 'i', $industryTableAlias = 'ii')
    {
        if ($this->internshipUsesNormalizedSchema()) {
            return "LEFT JOIN internship_industries {$industryTableAlias} ON {$industryTableAlias}.industry_id = {$tableAlias}.industry_id";
        }

        return '';
    }
    /* =====================================================
       COMPANY PROFILE
    ===================================================== */
    public function getCompany($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                Email,
                Name,
                companytype,
                contactPersonName,
                contactNumber,
                website,
                location,
                Status
             FROM company
             WHERE TRIM(Email) = TRIM(?)
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function updateCompany(
        $email,
        $currentName,
        $name,
        $industry,
        $contactPerson,
        $contactNumber,
        $website,
        $location
    ) {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                "UPDATE company
                 SET
                    Name = ?,
                    companytype = ?,
                    contactPersonName = ?,
                    contactNumber = ?,
                    website = ?,
                    location = ?
                 WHERE TRIM(Email) = TRIM(?)"
            );

            $stmt->bind_param(
                "sssssss",
                $name,
                $industry,
                $contactPerson,
                $contactNumber,
                $website,
                $location,
                $email
            );

            $stmt->execute();

            if (
                $stmt->affected_rows < 1
                && trim($currentName) !== trim($name)
            ) {
                throw new Exception('Company profile could not be updated.');
            }

            if (trim($currentName) !== trim($name) && !$this->internshipUsesNormalizedSchema()) {
                $internshipStmt = $this->conn->prepare(
                    "UPDATE internships
                     SET company = ?
                     WHERE TRIM(company) = TRIM(?)"
                );

                $internshipStmt->bind_param(
                    "ss",
                    $name,
                    $currentName
                );

                $internshipStmt->execute();
            }

            $this->conn->commit();
            return true;

        } catch (Exception $exception) {
            $this->conn->rollback();
            throw $exception;
        }
    }

    /* =====================================================
       COMPANY DASHBOARD
    ===================================================== */

    public function getDashboardCounts($companyName)
    {
        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT
                        COUNT(DISTINCT i.id) AS total_internships,
                        COUNT(
                            DISTINCT CASE
                                WHEN i.deadline_date >= CURDATE()
                                THEN i.id
                            END
                        ) AS active_internships,
                        COUNT(ia.application_id) AS total_applications,
                        SUM(
                            CASE
                                WHEN LOWER(TRIM(ia.status)) = 'accepted'
                                THEN 1
                                ELSE 0
                            END
                        ) AS accepted,
                        SUM(
                            CASE
                                WHEN LOWER(ia.status) LIKE '%interview%'
                                THEN 1
                                ELSE 0
                            END
                        ) AS interviews,
                        SUM(
                            CASE
                                WHEN LOWER(ia.status) IN ('selected', 'accepted', 'hired')
                                THEN 1
                                ELSE 0
                            END
                        ) AS hired,
                        SUM(
                            CASE
                                WHEN ia.applied_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                THEN 1
                                ELSE 0
                            END
                        ) AS new_this_week
                    FROM internships i
                    LEFT JOIN company c ON c.Email = i.company_email
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)";
        } else {
            $sql = "SELECT
                        COUNT(DISTINCT i.id) AS total_internships,
                        COUNT(
                            DISTINCT CASE
                                WHEN STR_TO_DATE(i.deadline, '%b %e, %Y') >= CURDATE()
                                THEN i.id
                            END
                        ) AS active_internships,
                        COUNT(ia.application_id) AS total_applications,
                        SUM(
                            CASE
                                WHEN LOWER(TRIM(ia.status)) = 'accepted'
                                THEN 1
                                ELSE 0
                            END
                        ) AS accepted,
                        SUM(
                            CASE
                                WHEN LOWER(ia.status) LIKE '%interview%'
                                THEN 1
                                ELSE 0
                            END
                        ) AS interviews,
                        SUM(
                            CASE
                                WHEN LOWER(ia.status) IN ('selected', 'accepted', 'hired')
                                THEN 1
                                ELSE 0
                            END
                        ) AS hired,
                        SUM(
                            CASE
                                WHEN ia.applied_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                THEN 1
                                ELSE 0
                            END
                        ) AS new_this_week
                    FROM internships i
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(i.company) = TRIM(?)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $companyName);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        foreach ($row as $key => $value) {
            $row[$key] = (int) (isset($value) ? $value : 0);
        }

        return $row;
    }

    public function getRecentApplications($companyName, $limit = 4)
    {
        $limit = max(1, (int) $limit);

        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT
                        ia.application_id,
                        ia.status,
                        ia.applied_date,
                        i.title,
                        s.Name AS student_name,
                        s.University,
                        s.profile_image
                    FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    LEFT JOIN company c ON c.Email = i.company_email
                    INNER JOIN student s ON s.Email = ia.Email
                    WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)
                    ORDER BY ia.application_id DESC
                    LIMIT {$limit}";
        } else {
            $sql = "SELECT
                        ia.application_id,
                        ia.status,
                        ia.applied_date,
                        i.title,
                        s.Name AS student_name,
                        s.University,
                        s.profile_image
                    FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    INNER JOIN student s ON s.Email = ia.Email
                    WHERE TRIM(i.company) = TRIM(?)
                    ORDER BY ia.application_id DESC
                    LIMIT {$limit}";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $companyName);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateCompanyApplicationDecision($applicationId, $companyName, $decision, $details = [])
    {
        $decision = ucfirst(strtolower(trim((string) $decision)));
        if (!in_array($decision, ['Accepted', 'Disqualified'], true)) {
            return false;
        }

        if ($this->internshipUsesNormalizedSchema()) {
            $currentSql = "SELECT ia.application_id, ia.Email, ia.status, i.title, c.Name AS company_name
                           FROM internship_applications ia
                           INNER JOIN internships i ON i.id = ia.internship_id
                           LEFT JOIN company c ON c.Email = i.company_email
                           WHERE ia.application_id = ?
                             AND TRIM(COALESCE(c.Name, '')) = TRIM(?)
                           LIMIT 1";
        } else {
            $currentSql = "SELECT ia.application_id, ia.Email, ia.status, i.title, i.company
                           FROM internship_applications ia
                           INNER JOIN internships i ON i.id = ia.internship_id
                           WHERE ia.application_id = ?
                             AND TRIM(i.company) = TRIM(?)
                           LIMIT 1";
        }

        $currentStmt = $this->conn->prepare($currentSql);
        if (!$currentStmt) return false;
        $currentStmt->bind_param("is", $applicationId, $companyName);
        if (!$currentStmt->execute()) return false;
        $current = $currentStmt->get_result()->fetch_assoc();
        if (!$current) return false;

        try {
            $this->conn->begin_transaction();
            if ($decision === 'Accepted') {
                $interviewDate = !empty($details['interview_date']) ? $details['interview_date'] : null;
                $interviewTime = !empty($details['interview_time']) ? trim($details['interview_time']) : null;
                $interviewType = !empty($details['interview_type']) ? trim($details['interview_type']) : 'Video Interview (Google Meet / Zoom)';
                $interviewLink = !empty($details['interview_link']) ? trim($details['interview_link']) : null;
                $interviewNotes = !empty($details['interview_notes']) ? trim($details['interview_notes']) : null;

                if ($this->tableExists('internship_interviews')) {
                    $sql = "INSERT INTO internship_interviews (
                                application_id, interview_date, interview_time, interview_type, interview_link, interview_notes
                            ) VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                interview_date = VALUES(interview_date),
                                interview_time = VALUES(interview_time),
                                interview_type = VALUES(interview_type),
                                interview_link = VALUES(interview_link),
                                interview_notes = VALUES(interview_notes)";
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) throw new Exception('Could not save interview data.');
                    $stmt->bind_param("isssss", $applicationId, $interviewDate, $interviewTime, $interviewType, $interviewLink, $interviewNotes);
                    if (!$stmt->execute()) throw new Exception('Could not save interview data.');

                    $updateSql = "UPDATE internship_applications
                                  SET status = 'Accepted', disqualification_reason = NULL
                                  WHERE application_id = ?";
                    $updateStmt = $this->conn->prepare($updateSql);
                    if (!$updateStmt) throw new Exception('Could not update application status.');
                    $updateStmt->bind_param("i", $applicationId);
                    if (!$updateStmt->execute()) throw new Exception('Could not update application status.');
                } else {
                    $sql = "UPDATE internship_applications
                            SET status = 'Accepted', interview_date = ?, interview_time = ?,
                                interview_type = ?, interview_link = ?, interview_notes = ?,
                                disqualification_reason = NULL
                            WHERE application_id = ?";
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) throw new Exception('Could not update application status.');
                    $stmt->bind_param("sssssi", $interviewDate, $interviewTime, $interviewType, $interviewLink, $interviewNotes, $applicationId);
                    if (!$stmt->execute()) throw new Exception('Could not update application status.');
                }

                $studentEmail = $current['Email'];
                $internshipTitle = $current['title'];
                $notificationTitle = "Interview Scheduled: " . $internshipTitle;
                $notifTitle = function_exists('mb_substr') ? mb_substr($notificationTitle, 0, 100, 'UTF-8') : substr($notificationTitle, 0, 100);
                $notifMsg = "Your application for " . $internshipTitle . " at " . $companyName . " has been accepted.";
                if ($interviewDate) {
                    $notifMsg .= " Interview date: " . $interviewDate . ($interviewTime ? " at " . $interviewTime : "") . ".";
                }
                if ($interviewLink) {
                    $notifMsg .= " Location / Link: " . $interviewLink . ".";
                }
                $nStmt = $this->conn->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, 'application', 'Unread', NOW())");
                if (!$nStmt) throw new Exception('Could not prepare student notification.');
                $nStmt->bind_param("sss", $studentEmail, $notifTitle, $notifMsg);
                if (!$nStmt->execute()) throw new Exception('Could not notify student.');

                $this->conn->commit();
                return true;
            }

            $reason = !empty($details['disqualification_reason']) ? trim($details['disqualification_reason']) : 'Not suitable for the role';
            if (!empty($details['disqualification_notes'])) {
                $reason .= " - " . trim($details['disqualification_notes']);
            }

            if ($this->tableExists('internship_interviews')) {
                $deleteInterviewStmt = $this->conn->prepare("DELETE FROM internship_interviews WHERE application_id = ?");
                if (!$deleteInterviewStmt) throw new Exception('Could not remove interview.');
                $deleteInterviewStmt->bind_param("i", $applicationId);
                if (!$deleteInterviewStmt->execute()) throw new Exception('Could not remove interview.');
            }

            $sql = "UPDATE internship_applications
                    SET status = 'Disqualified', disqualification_reason = ?
                    WHERE application_id = ?";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) throw new Exception('Could not update application status.');
            $stmt->bind_param("si", $reason, $applicationId);
            if (!$stmt->execute()) throw new Exception('Could not update application status.');

            $studentEmail = $current['Email'];
            $internshipTitle = $current['title'];
            $notificationTitle = "Application Update: " . $internshipTitle;
            $notifTitle = function_exists('mb_substr') ? mb_substr($notificationTitle, 0, 100, 'UTF-8') : substr($notificationTitle, 0, 100);
            $notifMsg = "Your application for " . $internshipTitle . " at " . $companyName . " was not selected at this time.";
            $nStmt = $this->conn->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, 'application', 'Unread', NOW())");
            if (!$nStmt) throw new Exception('Could not prepare student notification.');
            $nStmt->bind_param("sss", $studentEmail, $notifTitle, $notifMsg);
            if (!$nStmt->execute()) throw new Exception('Could not notify student.');

            $this->conn->commit();
            return true;
        } catch (Exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }

    public function resetCompanyApplication($applicationId, $companyName)
    {
        $applicationId = (int) $applicationId;
        if ($applicationId < 1) return false;
        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT ia.Email, i.title FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    LEFT JOIN company c ON c.Email = i.company_email
                    WHERE ia.application_id = ? AND TRIM(COALESCE(c.Name, '')) = TRIM(?) LIMIT 1";
        } else {
            $sql = "SELECT ia.Email, i.title FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    WHERE ia.application_id = ? AND TRIM(i.company) = TRIM(?) LIMIT 1";
        }
        $check = $this->conn->prepare($sql);
        if (!$check) return false;
        $check->bind_param('is', $applicationId, $companyName);
        if (!$check->execute()) return false;
        $current = $check->get_result()->fetch_assoc();
        if (!$current) return false;

        try {
            $this->conn->begin_transaction();
            if ($this->tableExists('internship_interviews')) {
                $delete = $this->conn->prepare('DELETE FROM internship_interviews WHERE application_id = ?');
                if (!$delete) throw new Exception('Could not prepare interview removal.');
                $delete->bind_param('i', $applicationId);
                if (!$delete->execute()) throw new Exception('Could not remove interview.');
            }
            $update = $this->conn->prepare("UPDATE internship_applications SET status = 'Applied', disqualification_reason = NULL WHERE application_id = ?");
            if (!$update) throw new Exception('Could not prepare application reset.');
            $update->bind_param('i', $applicationId);
            if (!$update->execute()) throw new Exception('Could not reset application.');
            $notificationTitle = 'Application Update: ' . $current['title'];
            $title = function_exists('mb_substr') ? mb_substr($notificationTitle, 0, 100, 'UTF-8') : substr($notificationTitle, 0, 100);
            $message = 'Your application for ' . $current['title'] . ' at ' . $companyName . ' is back under review.';
            $notify = $this->conn->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, 'application', 'Unread', NOW())");
            if (!$notify) throw new Exception('Could not prepare student notification.');
            $notify->bind_param('sss', $current['Email'], $title, $message);
            if (!$notify->execute()) throw new Exception('Could not notify student.');
            $this->conn->commit();
            return true;
        } catch (Exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }

    public function getRecentInternships($companyName, $limit = 2)
    {
        $limit = max(1, (int) $limit);

        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT
                        i.id,
                        i.title,
                        c.Name AS company,
                        ii.industry_name AS industry,
                        i.description,
                        i.cover_image,
                        i.logo_text,
                        i.logo_style,
                        (SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ')
                         FROM internship_tech_tag_map tm
                         JOIN internship_tech_tags t ON t.tag_id = tm.tag_id
                         WHERE tm.internship_id = i.id) AS tech_tags,
                        CASE
                            WHEN i.duration_value IS NULL THEN NULL
                            WHEN i.duration_unit IS NULL THEN CAST(i.duration_value AS CHAR)
                            ELSE CONCAT(i.duration_value, ' ', i.duration_unit)
                        END AS duration,
                        DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                        COUNT(ia.application_id) AS applicant_count
                    FROM internships i
                    LEFT JOIN company c ON c.Email = i.company_email
                    LEFT JOIN internship_industries ii ON ii.industry_id = i.industry_id
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)
                    GROUP BY i.id, i.title, c.Name, ii.industry_name, i.description, i.cover_image, i.logo_text, i.logo_style, i.duration_value, i.duration_unit, i.deadline_date
                    ORDER BY i.id DESC
                    LIMIT {$limit}";
        } else {
            $sql = "SELECT
                        i.*,
                        COUNT(ia.application_id) AS applicant_count
                    FROM internships i
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(i.company) = TRIM(?)
                    GROUP BY i.id
                    ORDER BY i.id DESC
                    LIMIT {$limit}";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $companyName);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getInterviewQueue($companyName, $limit = 3)
    {
        $limit = max(1, (int) $limit);

        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT
                        ia.application_id,
                        ia.applied_date,
                        i.title,
                        s.Name AS student_name
                    FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    LEFT JOIN company c ON c.Email = i.company_email
                    INNER JOIN student s ON s.Email = ia.Email
                    WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)
                      AND LOWER(TRIM(ia.status)) LIKE '%interview%'
                    ORDER BY ia.applied_date ASC, ia.application_id ASC
                    LIMIT {$limit}";
        } else {
            $sql = "SELECT
                        ia.application_id,
                        ia.applied_date,
                        i.title,
                        s.Name AS student_name
                    FROM internship_applications ia
                    INNER JOIN internships i ON i.id = ia.internship_id
                    INNER JOIN student s ON s.Email = ia.Email
                    WHERE TRIM(i.company) = TRIM(?)
                      AND LOWER(TRIM(ia.status)) LIKE '%interview%'
                    ORDER BY ia.applied_date ASC, ia.application_id ASC
                    LIMIT {$limit}";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $companyName);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =====================================================
       INTERNSHIP CRUD
    ===================================================== */

    /* =========================
       READ ALL COMPANY INTERNSHIPS
    ========================== */

    public function getCompanyInternships($companyName)
    {
        if ($this->internshipUsesNormalizedSchema()) {
            $sql = "SELECT
                        i.id,
                        i.title,
                        c.Name AS company,
                        ii.industry_name AS industry,
                        i.logo_text,
                        i.logo_style,
                        i.description,
                        i.cover_image,
                        i.academic_year,
                        i.experience_level,
                        i.vacancies,
                        i.internship_type,
                        i.work_mode,
                        i.location,
                        i.start_date,
                        i.paid_status,
                        i.stipend,
                        i.responsibilities,
                        i.benefits,
                        i.supporting_document,
                        i.status,
                        i.reactivation_status,
                        (SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ')
                         FROM internship_tech_tag_map tm
                         JOIN internship_tech_tags t ON t.tag_id = tm.tag_id
                         WHERE tm.internship_id = i.id) AS tech_tags,
                        CASE
                            WHEN i.duration_value IS NULL THEN NULL
                            WHEN i.duration_unit IS NULL THEN CAST(i.duration_value AS CHAR)
                            ELSE CONCAT(i.duration_value, ' ', i.duration_unit)
                        END AS duration,
                        DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                        COUNT(ia.application_id) AS applicant_count
                    FROM internships i
                    LEFT JOIN company c ON c.Email = i.company_email
                    LEFT JOIN internship_industries ii ON ii.industry_id = i.industry_id
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(COALESCE(c.Name, '')) = TRIM(?)
                    GROUP BY i.id, i.title, c.Name, ii.industry_name, i.logo_text, i.logo_style, i.description, i.cover_image,
                             i.academic_year, i.experience_level, i.vacancies, i.internship_type, i.work_mode, i.location,
                             i.start_date, i.paid_status, i.stipend, i.responsibilities, i.benefits, i.supporting_document,
                             i.status, i.reactivation_status, i.duration_value, i.duration_unit, i.deadline_date
                    ORDER BY i.id DESC";
        } else {
            $sql = "SELECT
                        i.*,
                        COUNT(ia.application_id) AS applicant_count
                    FROM internships i
                    LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                    WHERE TRIM(i.company) = TRIM(?)
                    GROUP BY i.id
                    ORDER BY i.id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $companyName);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =========================
       READ ONE INTERNSHIP
    ========================== */

    public function getInternshipById($id, $companyName = null)
    {
        $id = (int) $id;

        if ($this->internshipUsesNormalizedSchema()) {
            if (!empty($companyName)) {
                $sql = "SELECT
                            i.id,
                            i.title,
                            c.Name AS company,
                            ii.industry_name AS industry,
                            i.company_email,
                            i.industry_id,
                            i.description,
                            i.cover_image,
                            i.logo_text,
                            i.logo_style,
                            i.academic_year,
                            i.experience_level,
                            i.vacancies,
                            i.internship_type,
                            i.work_mode,
                            i.location,
                            i.start_date,
                            i.paid_status,
                            i.stipend,
                            i.responsibilities,
                            i.benefits,
                            i.supporting_document,
                            i.status,
                            i.reactivation_status,
                            (SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ')
                             FROM internship_tech_tag_map tm
                             JOIN internship_tech_tags t ON t.tag_id = tm.tag_id
                             WHERE tm.internship_id = i.id) AS tech_tags,
                            CASE
                                WHEN i.duration_value IS NULL THEN NULL
                                WHEN i.duration_unit IS NULL THEN CAST(i.duration_value AS CHAR)
                                ELSE CONCAT(i.duration_value, ' ', i.duration_unit)
                            END AS duration,
                            DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                            COUNT(ia.application_id) AS applicant_count
                        FROM internships i
                        LEFT JOIN company c ON c.Email = i.company_email
                        LEFT JOIN internship_industries ii ON ii.industry_id = i.industry_id
                        LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                        WHERE i.id = ?
                          AND TRIM(COALESCE(c.Name, '')) = TRIM(?)
                        GROUP BY i.id
                        LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) return false;
                $stmt->bind_param("is", $id, $companyName);
            } else {
                $sql = "SELECT
                            i.id,
                            i.title,
                            c.Name AS company,
                            ii.industry_name AS industry,
                            i.company_email,
                            i.industry_id,
                            i.description,
                            i.cover_image,
                            i.logo_text,
                            i.logo_style,
                            i.academic_year,
                            i.experience_level,
                            i.vacancies,
                            i.internship_type,
                            i.work_mode,
                            i.location,
                            i.start_date,
                            i.paid_status,
                            i.stipend,
                            i.responsibilities,
                            i.benefits,
                            i.supporting_document,
                            i.status,
                            i.reactivation_status,
                            (SELECT GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name SEPARATOR ', ')
                             FROM internship_tech_tag_map tm
                             JOIN internship_tech_tags t ON t.tag_id = tm.tag_id
                             WHERE tm.internship_id = i.id) AS tech_tags,
                            CASE
                                WHEN i.duration_value IS NULL THEN NULL
                                WHEN i.duration_unit IS NULL THEN CAST(i.duration_value AS CHAR)
                                ELSE CONCAT(i.duration_value, ' ', i.duration_unit)
                            END AS duration,
                            DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                            COUNT(ia.application_id) AS applicant_count
                        FROM internships i
                        LEFT JOIN company c ON c.Email = i.company_email
                        LEFT JOIN internship_industries ii ON ii.industry_id = i.industry_id
                        LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                        WHERE i.id = ?
                        GROUP BY i.id
                        LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) return false;
                $stmt->bind_param("i", $id);
            }
        } else {
            if (!empty($companyName)) {
                $sql = "SELECT i.*, COUNT(ia.application_id) AS applicant_count
                        FROM internships i
                        LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                        WHERE i.id = ? AND TRIM(i.company) = TRIM(?)
                        GROUP BY i.id
                        LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) return false;
                $stmt->bind_param("is", $id, $companyName);
            } else {
                $sql = "SELECT i.*, COUNT(ia.application_id) AS applicant_count
                        FROM internships i
                        LEFT JOIN internship_applications ia ON ia.internship_id = i.id
                        WHERE i.id = ?
                        GROUP BY i.id
                        LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) return false;
                $stmt->bind_param("i", $id);
            }
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /* =========================
       CREATE INTERNSHIP
    ========================== */

    public function createInternship(
        $title,
        $companyName,
        $industry,
        $description,
        $coverImage,
        $techTags,
        $academicYear,
        $experienceLevel,
        $vacancies,
        $duration,
        $internshipType,
        $workMode,
        $location,
        $startDate,
        $deadline,
        $paidStatus,
        $stipend,
        $responsibilities,
        $benefits,
        $supportingDocument
    ) {
        $companyEmail = $this->resolveCompanyEmailByName($companyName);
        if ($companyEmail === null && $this->internshipUsesNormalizedSchema()) {
            return false;
        }

        $this->conn->begin_transaction();

        try {
            if ($this->internshipUsesNormalizedSchema()) {
                $industryId = $this->upsertIndustryId($industry);
                [$durationValue, $durationUnit] = $this->parseDurationParts($duration);
                $deadlineDate = $this->parseDeadlineDate($deadline);

                $sql = "INSERT INTO internships (
                            title,
                            company_email,
                            industry_id,
                            description,
                            cover_image,
                            academic_year,
                            experience_level,
                            vacancies,
                            duration_value,
                            duration_unit,
                            internship_type,
                            work_mode,
                            location,
                            start_date,
                            deadline_date,
                            paid_status,
                            stipend,
                            responsibilities,
                            benefits,
                            supporting_document,
                            status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to prepare internship insert.');
                }

                $stmt->bind_param(
                    "ssissssiisssssssdsss",
                    $title,
                    $companyEmail,
                    $industryId,
                    $description,
                    $coverImage,
                    $academicYear,
                    $experienceLevel,
                    $vacancies,
                    $durationValue,
                    $durationUnit,
                    $internshipType,
                    $workMode,
                    $location,
                    $startDate,
                    $deadlineDate,
                    $paidStatus,
                    $stipend,
                    $responsibilities,
                    $benefits,
                    $supportingDocument
                );
                if (!$stmt->execute()) {
                    throw new Exception('Unable to create internship.');
                }

                $internshipId = $this->conn->insert_id;
                $this->persistInternshipTechTags($internshipId, $techTags);
            } else {
                $sql = "INSERT INTO internships (
                            title, company, industry, description, cover_image,
                            tech_tags, academic_year, experience_level, vacancies,
                            duration, internship_type, work_mode, location, start_date,
                            deadline, paid_status, stipend, responsibilities, benefits,
                            supporting_document
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to prepare internship insert.');
                }

                $stmt->bind_param(
                    "ssssssssisssssssdsss",
                    $title,
                    $companyName,
                    $industry,
                    $description,
                    $coverImage,
                    $techTags,
                    $academicYear,
                    $experienceLevel,
                    $vacancies,
                    $duration,
                    $internshipType,
                    $workMode,
                    $location,
                    $startDate,
                    $deadline,
                    $paidStatus,
                    $stipend,
                    $responsibilities,
                    $benefits,
                    $supportingDocument
                );
                if (!$stmt->execute()) {
                    throw new Exception('Unable to create internship.');
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }

    public function updateInternship(
        $id,
        $companyName,
        $title,
        $industry,
        $description,
        $coverImage,
        $techTags,
        $academicYear,
        $experienceLevel,
        $vacancies,
        $duration,
        $internshipType,
        $workMode,
        $location,
        $startDate,
        $deadline,
        $paidStatus,
        $stipend,
        $responsibilities,
        $benefits,
        $supportingDocument
    ) {
        $this->conn->begin_transaction();

        try {
            if ($this->internshipUsesNormalizedSchema()) {
                $companyEmail = $this->resolveCompanyEmailByName($companyName);
                if ($companyEmail === null) {
                    throw new Exception('Company not found.');
                }

                $industryId = $this->upsertIndustryId($industry);
                [$durationValue, $durationUnit] = $this->parseDurationParts($duration);
                $deadlineDate = $this->parseDeadlineDate($deadline);

                $sql = "UPDATE internships
                        SET title = ?,
                            industry_id = ?,
                            description = ?,
                            cover_image = ?,
                            academic_year = ?,
                            experience_level = ?,
                            vacancies = ?,
                            duration_value = ?,
                            duration_unit = ?,
                            internship_type = ?,
                            work_mode = ?,
                            location = ?,
                            start_date = ?,
                            deadline_date = ?,
                            paid_status = ?,
                            stipend = ?,
                            responsibilities = ?,
                            benefits = ?,
                            supporting_document = ?
                        WHERE id = ?
                          AND company_email = ?";

                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to prepare internship update.');
                }

                $stmt->bind_param(
                    "sissssiisssssssdsssis",
                    $title,
                    $industryId,
                    $description,
                    $coverImage,
                    $academicYear,
                    $experienceLevel,
                    $vacancies,
                    $durationValue,
                    $durationUnit,
                    $internshipType,
                    $workMode,
                    $location,
                    $startDate,
                    $deadlineDate,
                    $paidStatus,
                    $stipend,
                    $responsibilities,
                    $benefits,
                    $supportingDocument,
                    $id,
                    $companyEmail
                );
                if (!$stmt->execute()) {
                    throw new Exception('Unable to update internship.');
                }

                $this->persistInternshipTechTags((int) $id, $techTags);
            } else {
                $sql = "UPDATE internships
                        SET
                            title = ?,
                            industry = ?,
                            description = ?,
                            cover_image = ?,
                            tech_tags = ?,
                            academic_year = ?,
                            experience_level = ?,
                            vacancies = ?,
                            duration = ?,
                            internship_type = ?,
                            work_mode = ?,
                            location = ?,
                            start_date = ?,
                            deadline = ?,
                            paid_status = ?,
                            stipend = ?,
                            responsibilities = ?,
                            benefits = ?,
                            supporting_document = ?
                        WHERE id = ?
                          AND TRIM(company) = TRIM(?)";

                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to prepare internship update.');
                }

                $stmt->bind_param(
                    "sssssssisssssssdsssis",
                    $title,
                    $industry,
                    $description,
                    $coverImage,
                    $techTags,
                    $academicYear,
                    $experienceLevel,
                    $vacancies,
                    $duration,
                    $internshipType,
                    $workMode,
                    $location,
                    $startDate,
                    $deadline,
                    $paidStatus,
                    $stipend,
                    $responsibilities,
                    $benefits,
                    $supportingDocument,
                    $id,
                    $companyName
                );
                if (!$stmt->execute()) {
                    throw new Exception('Unable to update internship.');
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }

    public function deleteInternship(
        $id,
        $companyName
    ) {
        $this->conn->begin_transaction();

        try {
            if ($this->internshipUsesNormalizedSchema()) {
                $companyEmail = $this->resolveCompanyEmailByName($companyName);
                $sql = "DELETE FROM internships
                        WHERE id = ?
                          AND company_email = ?
                          AND (
                              LOWER(COALESCE(status, '')) = 'terminated'
                              OR (
                                  LOWER(COALESCE(status, '')) <> 'suspended'
                                  AND NOT EXISTS (
                                      SELECT 1
                                      FROM internship_applications ia
                                      WHERE ia.internship_id = internships.id
                                  )
                              )
                          )";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to delete internship.');
                }
                $stmt->bind_param("is", $id, $companyEmail);
                if (!$stmt->execute()) {
                    throw new Exception('Unable to delete internship.');
                }
            } else {
                $sql = "DELETE FROM internships
                        WHERE
                            id = ?
                            AND TRIM(company) = TRIM(?)
                            AND (
                                LOWER(COALESCE(internships.status, '')) = 'terminated'
                                OR (
                                    LOWER(COALESCE(internships.status, '')) <> 'suspended'
                                    AND NOT EXISTS (
                                        SELECT 1
                                        FROM internship_applications ia
                                        WHERE ia.internship_id = internships.id
                                    )
                                )
                            )";

                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Unable to delete internship.');
                }
                $stmt->bind_param("is", $id, $companyName);
                if (!$stmt->execute()) {
                    throw new Exception('Unable to delete internship.');
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }

    public function setCompanyInternshipStatus($id, $companyName, $newStatus)
    {
        $newStatus = ucfirst(strtolower(trim((string) $newStatus)));
        if (!in_array($newStatus, ['Active', 'Closed'], true)) {
            return false;
        }

        $internship = $this->getInternshipById($id, $companyName);
        if (!$internship || in_array(strtolower($internship['status'] ?? ''), ['suspended', 'terminated'], true)) {
            return false;
        }

        $deadlineValue = $internship['deadline_date'] ?? $internship['deadline'] ?? null;
        $deadline = $deadlineValue !== null && $deadlineValue !== '' ? strtotime($deadlineValue) : false;
        if ($newStatus === 'Active' && ($deadline === false || $deadline < strtotime('today'))) {
            return false;
        }
        $currentStatus = ucfirst(strtolower(trim($internship['status'] ?? '')));
        if ($currentStatus === $newStatus) return true;

        if ($this->internshipUsesNormalizedSchema()) {
            $companyEmail = $this->resolveCompanyEmailByName($companyName);
            $stmt = $this->conn->prepare("UPDATE internships SET status = ? WHERE id = ? AND company_email = ? AND LOWER(status) IN ('active', 'closed')");
            if (!$stmt) return false;
            $stmt->bind_param("sis", $newStatus, $id, $companyEmail);
        } else {
            $stmt = $this->conn->prepare("UPDATE internships SET status = ? WHERE id = ? AND TRIM(company) = TRIM(?) AND LOWER(status) IN ('active', 'closed')");
            if (!$stmt) return false;
            $stmt->bind_param("sis", $newStatus, $id, $companyName);
        }

        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function requestInternshipReactivation($id, $companyName)
    {
        if ($this->internshipUsesNormalizedSchema()) {
            $companyEmail = $this->resolveCompanyEmailByName($companyName);
            $stmt = $this->conn->prepare("UPDATE internships SET reactivation_status = 'Requested' WHERE id = ? AND company_email = ? AND LOWER(status) = 'suspended' AND (reactivation_status IS NULL OR reactivation_status = '')");
            if (!$stmt) return false;
            $stmt->bind_param("is", $id, $companyEmail);
        } else {
            $stmt = $this->conn->prepare("UPDATE internships SET reactivation_status = 'Requested' WHERE id = ? AND TRIM(company) = TRIM(?) AND LOWER(status) = 'suspended' AND (reactivation_status IS NULL OR reactivation_status = '')");
            if (!$stmt) return false;
            $stmt->bind_param("is", $id, $companyName);
        }
        return $stmt->execute() && $stmt->affected_rows === 1;
    }
}
