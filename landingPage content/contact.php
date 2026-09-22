<?php
require_once __DIR__ . '/../Config/db.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if (!empty($full_name) && !empty($email) && !empty($message)) {
        // 1. Insert into contact_messages
        $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ssss", $full_name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success_msg = "Thank you! Your message has been sent successfully.";

                // 2. Notify all Administrators in the notifications table
                $adminRes = $conn->query("SELECT Email FROM user WHERE role = 'admin' AND status = 'Active'");
                if (!$adminRes || $adminRes->num_rows === 0) {
                    $adminRes = $conn->query("SELECT Email FROM user WHERE role = 'admin'");
                }

                if ($adminRes && $adminRes->num_rows > 0) {
                    $notifTitle = "New Contact Inquiry: " . (!empty($subject) ? $subject : 'Website Inquiry');
                    $notifMsg   = "From: " . $full_name . " (" . $email . ")\n\n" . $message;
                    $notifType  = "contact_inquiry";
                    $notifStatus = "Unread";

                    $notifStmt = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    if ($notifStmt) {
                        while ($adminRow = $adminRes->fetch_assoc()) {
                            $adminEmail = $adminRow['Email'];
                            $notifStmt->bind_param("sssss", $adminEmail, $notifTitle, $notifMsg, $notifType, $notifStatus);
                            $notifStmt->execute();
                        }
                        $notifStmt->close();
                    }
                }
            } else {
                $error_msg = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: " . $conn->error;
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

$page_css = "Assets/CSS/contact.css";
include '../Includes/header.php';
?>

<div class="contact-header">
    <h1>Get In Touch With SkillBridge</h1>
    <p>Have questions? Our team is here to help you bridge the gap between classroom and career.</p>
</div>

<div class="contact-container">
    <!-- Contact Info Column -->
    <div class="contact-info-col">
        <h2>Contact Information</h2>

        <div class="info-card">
            <div class="info-icon">✉️</div>
            <div class="info-details">
                <h4>Email</h4>
                <p>bridgeskill62@gmail.com</p>
            </div>
        </div>

        <div class="info-card">
            <div class="info-icon">📞</div>
            <div class="info-details">
                <h4>Phone</h4>
                <p>+94 XX XXX XXXX</p>
            </div>
        </div>

        <div class="info-card">
            <div class="info-icon">📍</div>
            <div class="info-details">
                <h4>Location</h4>
                <p>University Campus, Innovation Hub, Block C</p>
            </div>
        </div>

        <div class="map-placeholder">
            <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Map View" style="border-radius: 8px;">
        </div>
    </div>

    <!-- Contact Form Column -->
    <div class="contact-form-col">
        <h2>Send Us a Message</h2>
        <?php if (!empty($success_msg)): ?>
            <div style="background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $error_msg; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="john@university.edu" required>
                </div>
            </div>

            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="How can we help?">
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" placeholder="Tell us more about your inquiry..." required></textarea>
            </div>

            <button type="submit" name="submit_contact" class="btn-submit">Send Message <span>🚀</span></button>
        </form>
    </div>
</div>

<!-- FAQ Section -->
<div class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <p>Find quick answers to common queries about SkillBridge.</p>

    <div class="faq-grid">
        <div class="faq-card">
            <h4>How do I register? <span>⌄</span></h4>
            <p>Students and companies can register by clicking the "Sign Up" button. Students need to use their academia
                email, while companies should provide valid business credentials.</p>
        </div>

        <div class="faq-card">
            <h4>How can students join projects? <span>⌄</span></h4>
            <p>Once registered, navigate to the 'Projects' tab, filter by your skillset, and apply to open projects. Our
                matching algorithm also recommends projects based on your profile.</p>
        </div>

        <div class="faq-card">
            <h4>How can companies post internships? <span>⌄</span></h4>
            <p>Verified companies can access the dashboard to create detailed internship listings, specifying required
                skills, duration, and mentorship availability.</p>
        </div>

        <div class="faq-card">
            <h4>How are users verified? <span>⌄</span></h4>
            <p>We use a two-step verification process. Students are verified through their institutional portals, and
                companies undergo a background check to ensure quality professional opportunities.</p>
        </div>
    </div>
</div>

<?php include '../Includes/footer.php'; ?>