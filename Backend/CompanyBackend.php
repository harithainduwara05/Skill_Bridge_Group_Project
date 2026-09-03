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

                            WHEN LOWER(ia.status)
                            IN ('shortlisted', 'shortlist')

                            THEN 1

                            ELSE 0

                        END
                    )
                        AS shortlisted,

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
        $techTags,
        $duration,
        $deadline
    ) {

        $sql = "INSERT INTO internships
                (
                    title,
                    company,
                    industry,
                    tech_tags,
                    duration,
                    deadline
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )";


        $stmt =
            $this->conn->prepare($sql);


        $stmt->bind_param(
            "ssssss",
            $title,
            $companyName,
            $industry,
            $techTags,
            $duration,
            $deadline
        );


        return
            $stmt->execute();

    }



    /* =========================
       UPDATE INTERNSHIP
    ========================== */

    public function updateInternship(
        $id,
        $companyName,
        $title,
        $industry,
        $techTags,
        $duration,
        $deadline
    ) {

        $sql = "UPDATE internships

                SET

                    title = ?,
                    industry = ?,
                    tech_tags = ?,
                    duration = ?,
                    deadline = ?

                WHERE

                    id = ?

                    AND

                    TRIM(company) = TRIM(?)";


        $stmt =
            $this->conn->prepare($sql);


        $stmt->bind_param(
            "sssssis",
            $title,
            $industry,
            $techTags,
            $duration,
            $deadline,
            $id,
            $companyName
        );


        return
            $stmt->execute();

    }



    /* =========================
       DELETE INTERNSHIP
    ========================== */

    public function deleteInternship(
        $id,
        $companyName
    ) {

        $sql = "DELETE FROM internships

                WHERE

                    id = ?

                    AND

                    TRIM(company) = TRIM(?)";


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

}