<?php
/**
 * SkillBridge - Complaint Management Service
 * Handles complaint ticketing, investigation status updates, notifications, and analytics.
 */

require_once __DIR__ . '/AdminDB.php';

class ComplaintManagement extends AdminDB
{
    public function getUndismissedComplaintCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM complain WHERE status != 'DISMISSED'");
    }

    public function getComplaints()
    {
        return $this->runQuery("SELECT * FROM complain")->fetch_all(MYSQLI_ASSOC);
    }

    public function getUrgentComplaints($limit = 3)
    {
        $sql = "SELECT * FROM complain 
                WHERE status != 'DISMISSED' 
                ORDER BY 
                    CASE priority 
                        WHEN 'URGENT' THEN 1 
                        WHEN 'HIGH' THEN 2 
                        WHEN 'MEDIUM' THEN 3 
                        WHEN 'LOW' THEN 4 
                        ELSE 5 
                    END ASC, 
                    create_at DESC, 
                    id DESC 
                LIMIT ?";
        return $this->runQuery($sql, "i", $limit)->fetch_all(MYSQLI_ASSOC);
    }

    public function dismissComplaint($id)
    {
        return $this->runAction("UPDATE complain SET status = 'DISMISSED', update_at = CURRENT_TIMESTAMP WHERE id = ?", "i", $id);
    }

    public function updateComplaintStatus($id, $status, $notes = '')
    {
        $allowedStatuses = ['PENDING', 'IN_REVIEW', 'RESOLVED', 'DISMISSED'];
        $cleanStatus = strtoupper(str_replace(' ', '_', $status));
        if ($cleanStatus === 'IN_PROGRESS') {
            $cleanStatus = 'IN_REVIEW';
        }
        if (!in_array($cleanStatus, $allowedStatuses)) {
            return false;
        }

        // Fetch complaint details to get student/user email
        $cmp = $this->getComplaintById($id);

        if (!empty($notes)) {
            $updated = $this->runAction("UPDATE complain SET status = ?, resolution_notes = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?", "ssi", $cleanStatus, $notes, $id);
        } else {
            $updated = $this->runAction("UPDATE complain SET status = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?", "si", $cleanStatus, $id);
        }

        // Complainant Notification: Notify complainant regarding status and resolution/dismissal notes
        if ($updated && $cmp && !empty($cmp['email'])) {
            $recipientEmail = $cmp['email'];
            $complaintTitle = $cmp['title'] ?? 'Complaint';
            $paddedId = '#CMP-' . str_pad($id, 4, '0', STR_PAD_LEFT);

            if ($cleanStatus === 'IN_REVIEW') {
                $notifTitle = "Complaint Under Investigation ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Admin update regarding \"$complaintTitle\": $notes"
                    : "Your reported issue ($paddedId) has been placed under active investigation by administrators.";
            } elseif ($cleanStatus === 'RESOLVED') {
                $notifTitle = "Complaint Resolved ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Resolution for \"$complaintTitle\": $notes"
                    : "Your reported issue ($paddedId) has been successfully resolved and finalized.";
            } elseif ($cleanStatus === 'DISMISSED') {
                $notifTitle = "Complaint Dismissed ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Your complaint \"$complaintTitle\" was dismissed. Reason: $notes"
                    : "Your reported issue ($paddedId) has been dismissed by administrators.";
            } else {
                $notifTitle = "Complaint Update ($paddedId)";
                $notifMsg = !empty($notes) ? $notes : "Your complaint status has been updated to $cleanStatus.";
            }

            $this->addNotification($recipientEmail, $notifTitle, $notifMsg, 'complaint');
        }

        return $updated;
    }

    public function getComplaintById($id)
    {
        $sql = "
        SELECT 
            c.id,
            c.email,
            c.title,
            c.discription,
            COALESCE(c.category, 'Technical') AS category,
            c.priority,
            c.status,
            c.resolution_notes,
            c.create_at,
            c.update_at,
            LOWER(COALESCE(u.role, 'user')) AS role,
            COALESCE(s.Name, comp.contactPersonName, o.contactPersonName, a.Name, 'Community Member') AS user_name,
            COALESCE(s.University, comp.Name, o.Name, 'SkillBridge') AS organization_name,
            s.profile_image AS profile_image
        FROM complain c
        LEFT JOIN user u ON c.email = u.Email
        LEFT JOIN student s ON c.email = s.Email
        LEFT JOIN company comp ON c.email = comp.Email
        LEFT JOIN organization o ON c.email = o.Email
        LEFT JOIN admin a ON c.email = a.Email
        WHERE c.id = ?
        LIMIT 1
        ";
        $res = $this->runQuery($sql, "i", $id);
        return $res ? $res->fetch_assoc() : null;
    }

    public function getAllComplaintsDetailed($status = 'all', $priority = 'all', $category = 'all', $search = '')
    {
        $sql = "
        SELECT 
            c.id,
            c.email,
            c.title,
            c.discription,
            COALESCE(c.category, 'Technical') AS category,
            c.priority,
            c.status,
            c.resolution_notes,
            c.create_at,
            c.update_at,
            LOWER(COALESCE(u.role, 'user')) AS role,
            COALESCE(s.Name, comp.contactPersonName, o.contactPersonName, a.Name, 'Community Member') AS user_name,
            COALESCE(s.University, comp.Name, o.Name, 'SkillBridge') AS organization_name,
            s.profile_image AS profile_image
        FROM complain c
        LEFT JOIN user u ON c.email = u.Email
        LEFT JOIN student s ON c.email = s.Email
        LEFT JOIN company comp ON c.email = comp.Email
        LEFT JOIN organization o ON c.email = o.Email
        LEFT JOIN admin a ON c.email = a.Email
        WHERE 1=1
        ";

        $params = [];
        $types = "";

        if (!empty($status) && $status !== 'all') {
            $mappedStatus = strtoupper(str_replace(' ', '_', $status));
            if ($mappedStatus === 'IN_PROGRESS') {
                $mappedStatus = 'IN_REVIEW';
            }
            $sql .= " AND c.status = ?";
            $params[] = $mappedStatus;
            $types .= "s";
        }

        if (!empty($priority) && $priority !== 'all') {
            $sql .= " AND c.priority = ?";
            $params[] = strtoupper($priority);
            $types .= "s";
        }

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND c.category = ?";
            $params[] = $category;
            $types .= "s";
        }

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql .= " AND (c.id LIKE ? OR c.email LIKE ? OR c.title LIKE ? OR c.discription LIKE ? OR s.Name LIKE ? OR comp.contactPersonName LIKE ? OR o.contactPersonName LIKE ? OR a.Name LIKE ?)";
            for ($i = 0; $i < 8; $i++) {
                $params[] = $searchTerm;
                $types .= "s";
            }
        }

        $sql .= " ORDER BY 
            CASE c.status
                WHEN 'PENDING' THEN 1
                WHEN 'IN_REVIEW' THEN 2
                WHEN 'RESOLVED' THEN 3
                WHEN 'DISMISSED' THEN 4
                ELSE 5
            END ASC,
            CASE c.priority 
                WHEN 'URGENT' THEN 1 
                WHEN 'HIGH' THEN 2 
                WHEN 'MEDIUM' THEN 3 
                WHEN 'LOW' THEN 4 
                ELSE 5 
            END ASC, 
            c.create_at DESC, 
            c.id DESC";

        if (!empty($types)) {
            return $this->runQuery($sql, $types, ...$params)->fetch_all(MYSQLI_ASSOC);
        }
        return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getComplaintStats()
    {
        $total = $this->runCount("SELECT COUNT(*) FROM complain");
        $pending = $this->runCount("SELECT COUNT(*) FROM complain WHERE status IN ('PENDING', 'IN_REVIEW')");
        $resolved = $this->runCount("SELECT COUNT(*) FROM complain WHERE status = 'RESOLVED'");
        
        $avgMinutesRes = $this->runQuery("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, create_at, update_at)) AS avg_mins 
            FROM complain 
            WHERE status = 'RESOLVED' AND update_at > create_at
        ")->fetch_assoc();
        
        if (!empty($avgMinutesRes['avg_mins']) && floatval($avgMinutesRes['avg_mins']) > 0) {
            $avgMins = round(floatval($avgMinutesRes['avg_mins']));
            $hours = floor($avgMins / 60);
            $mins = $avgMins % 60;
            $avgFormatted = "{$hours}h {$mins}m";
        } elseif ($resolved > 0) {
            $avgFormatted = "< 1h";
        } else {
            $avgFormatted = "N/A";
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'resolved' => $resolved,
            'avg_response_time' => $avgFormatted
        ];
    }
    public function getActiveComplainCount(){
        $cntRes=$this->runCount("SELECT COUNT(*) AS active_count FROM complain WHERE status NOT IN ('RESOLVED', 'DISMISSED')");
        return (int)$cntRes;
    }
}
?>
