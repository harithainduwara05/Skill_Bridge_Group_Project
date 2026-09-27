<?php



class CompanyManager

{

    private $conn;





    public function __construct($conn)

    {

        $this->conn = $conn;

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

                &&

                trim($currentName) !== trim($name)

            ) {



                throw new Exception(

                    'Company profile could not be updated.'

                );

            }







            /*

             If company name changes,

             update internship company name too.

            */



            if (trim($currentName) !== trim($name)) {



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



        $sql = "SELECT



                    COUNT(DISTINCT i.id)

                        AS total_internships,



                    COUNT(

                        DISTINCT CASE



                            WHEN

                            STR_TO_DATE(

                                i.deadline,

                                '%b %e, %Y'

                            ) >= CURDATE()



                            THEN i.id



                        END

                    )

                        AS active_internships,



                    COUNT(ia.application_id)

                        AS total_applications,



                    SUM(
                        CASE
                            WHEN LOWER(TRIM(ia.status)) = 'accepted'
                            THEN 1
                            ELSE 0
                        END
                    ) AS accepted,



                    SUM(

                        CASE



                            WHEN LOWER(ia.status)

                            LIKE '%interview%'



                            THEN 1



                            ELSE 0



                        END

                    )

                        AS interviews,



                    SUM(

                        CASE



                            WHEN LOWER(ia.status)

                            IN (

                                'selected',

                                'accepted',

                                'hired'

                            )



                            THEN 1



                            ELSE 0



                        END

                    )

                        AS hired,



                    SUM(

                        CASE



                            WHEN ia.applied_date >=

                            DATE_SUB(

                                CURDATE(),

                                INTERVAL 7 DAY

                            )



                            THEN 1



                            ELSE 0



                        END

                    )

                        AS new_this_week





                FROM internships i





                LEFT JOIN internship_applications ia



                    ON ia.internship_id = i.id





                WHERE TRIM(i.company) = TRIM(?)";





        $stmt = $this->conn->prepare($sql);





        $stmt->bind_param(

            "s",

            $companyName

        );





        $stmt->execute();





        $row =

            $stmt

            ->get_result()

            ->fetch_assoc();







        foreach ($row as $key => $value) {



            $row[$key] =

                (int) (

                    isset($value)

                    ? $value

                    : 0

                );



        }





        return $row;



    }







    public function getRecentApplications(

        $companyName,

        $limit = 4

    ) {



        $limit =

            max(

                1,

                (int) $limit

            );





        $sql = "SELECT



                    ia.application_id,

                    ia.status,

                    ia.applied_date,



                    i.title,



                    s.Name AS student_name,

                    s.University,

                    s.profile_image





                FROM internship_applications ia





                INNER JOIN internships i



                    ON i.id = ia.internship_id





                INNER JOIN student s



                    ON s.Email = ia.Email





                WHERE TRIM(i.company) = TRIM(?)





                ORDER BY ia.application_id DESC





                LIMIT {$limit}";





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "s",

            $companyName

        );





        $stmt->execute();





        return

            $stmt

            ->get_result()

            ->fetch_all(

                MYSQLI_ASSOC

            );



    }

    public function updateCompanyApplicationDecision($applicationId, $companyName, $decision)
    {
        $decision = ucfirst(strtolower(trim((string) $decision)));
        if (!in_array($decision, ['Accepted', 'Disqualified'], true)) {
            return false;
        }

        $currentSql = "SELECT ia.status
                       FROM internship_applications ia
                       INNER JOIN internships i ON i.id = ia.internship_id
                       WHERE ia.application_id = ?
                         AND TRIM(i.company) = TRIM(?)
                       LIMIT 1";
        $currentStmt = $this->conn->prepare($currentSql);
        if (!$currentStmt) return false;
        $currentStmt->bind_param("is", $applicationId, $companyName);
        if (!$currentStmt->execute()) return false;
        $current = $currentStmt->get_result()->fetch_assoc();
        if (!$current || strtolower(trim($current['status'] ?? '')) !== 'applied') return false;

        $sql = "UPDATE internship_applications ia
                INNER JOIN internships i ON i.id = ia.internship_id
                SET ia.status = ?
                WHERE ia.application_id = ?
                  AND TRIM(i.company) = TRIM(?)
                  AND LOWER(TRIM(ia.status)) = 'applied'";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("sis", $decision, $applicationId, $companyName);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }







    public function getRecentInternships(

        $companyName,

        $limit = 2

    ) {



        $limit =

            max(

                1,

                (int) $limit

            );





        $sql = "SELECT



                    i.*,



                    COUNT(ia.application_id)

                        AS applicant_count





                FROM internships i





                LEFT JOIN internship_applications ia



                    ON ia.internship_id = i.id





                WHERE TRIM(i.company) = TRIM(?)





                GROUP BY i.id





                ORDER BY i.id DESC





                LIMIT {$limit}";





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "s",

            $companyName

        );





        $stmt->execute();





        return

            $stmt

            ->get_result()

            ->fetch_all(

                MYSQLI_ASSOC

            );



    }







    public function getInterviewQueue(

        $companyName,

        $limit = 3

    ) {



        $limit =

            max(

                1,

                (int) $limit

            );





        $sql = "SELECT



                    ia.application_id,

                    ia.applied_date,



                    i.title,



                    s.Name AS student_name





                FROM internship_applications ia





                INNER JOIN internships i



                    ON i.id = ia.internship_id





                INNER JOIN student s



                    ON s.Email = ia.Email





                WHERE



                    TRIM(i.company) = TRIM(?)



                    AND



                    LOWER(

                        TRIM(ia.status)

                    ) LIKE '%interview%'





                ORDER BY



                    ia.applied_date ASC,



                    ia.application_id ASC





                LIMIT {$limit}";





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "s",

            $companyName

        );





        $stmt->execute();





        return

            $stmt

            ->get_result()

            ->fetch_all(

                MYSQLI_ASSOC

            );



    }







    /* =====================================================

       INTERNSHIP CRUD

    ===================================================== */





    /* =========================

       READ ALL COMPANY INTERNSHIPS

    ========================== */



    public function getCompanyInternships($companyName)

    {



        $sql = "SELECT



                    i.*,



                    COUNT(ia.application_id)

                        AS applicant_count





                FROM internships i





                LEFT JOIN internship_applications ia



                    ON ia.internship_id = i.id





                WHERE



                    TRIM(i.company) = TRIM(?)





                GROUP BY i.id





                ORDER BY i.id DESC";





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "s",

            $companyName

        );





        $stmt->execute();





        return

            $stmt

            ->get_result()

            ->fetch_all(

                MYSQLI_ASSOC

            );



    }







    /* =========================

       READ ONE INTERNSHIP

    ========================== */



    public function getInternshipById(

        $id,

        $companyName

    ) {



        $sql = "SELECT *



                FROM internships



                WHERE



                    id = ?



                    AND



                    TRIM(company) = TRIM(?)



                LIMIT 1";





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "is",

            $id,

            $companyName

        );





        $stmt->execute();





        return

            $stmt

            ->get_result()

            ->fetch_assoc();



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
        $sql = "INSERT INTO internships (
                    title, company, industry, description, cover_image,
                    tech_tags, academic_year, experience_level, vacancies,
                    duration, internship_type, work_mode, location, start_date,
                    deadline, paid_status, stipend, responsibilities, benefits,
                    supporting_document
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
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

        return $stmt->execute();
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
            return false;
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

        return $stmt->execute();
    }


    public function deleteInternship(

        $id,

        $companyName

    ) {



        $sql = "DELETE FROM internships



                WHERE



                    id = ?



                    AND



                    TRIM(company) = TRIM(?)

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





        $stmt =

            $this->conn->prepare($sql);





        $stmt->bind_param(

            "is",

            $id,

            $companyName

        );





        return

            $stmt->execute();



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
        $deadline = strtotime($internship['deadline'] ?? '');
        if ($newStatus === 'Active' && ($deadline === false || $deadline < strtotime('today'))) {
            return false;
        }
        $currentStatus = ucfirst(strtolower(trim($internship['status'] ?? '')));
        if ($currentStatus === $newStatus) return true;

        $stmt = $this->conn->prepare("UPDATE internships SET status = ? WHERE id = ? AND TRIM(company) = TRIM(?) AND LOWER(status) IN ('active', 'closed')");
        if (!$stmt) return false;
        $stmt->bind_param("sis", $newStatus, $id, $companyName);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function requestInternshipReactivation($id, $companyName)
    {
        $stmt = $this->conn->prepare("UPDATE internships SET reactivation_status = 'Requested' WHERE id = ? AND TRIM(company) = TRIM(?) AND LOWER(status) = 'suspended' AND (reactivation_status IS NULL OR reactivation_status = '')");
        if (!$stmt) return false;
        $stmt->bind_param("is", $id, $companyName);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }



}
