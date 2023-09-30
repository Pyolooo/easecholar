<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/send_message.css">
</head>
<body>
    <div class="message-box">
        <h2>Send Message</h2>
        <form>
            <label for="recipient">Recipient:</label>
            <select id="recipient" name="recipient">
                <option value="osa">Office of Student Affairs</option>
                <option value="applicant1">Applicant 1</option>
                <option value="applicant2">Applicant 2</option>
                <!-- Add more options as needed -->
            </select>
            
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
            
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="6" required></textarea>
            
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
