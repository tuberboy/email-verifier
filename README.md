# Email Verifier

**Email Verifier** is a tool designed to validate email addresses and check their deliverability. It ensures that the email addresses in your list are **valid, correctly formatted, and capable of receiving emails**, reducing bounce rates and improving the overall quality of your email communication.

## Features
- **Format Validation**: Verifies if the email address follows the correct syntax (e.g., `user@example.com`).
- **MX Records Check**: Confirms that the email domain has valid Mail Exchange (MX) records for email reception.
- **SMTP Check**: Ensures that the email server accepts incoming emails for the provided email address.
- **Catch-All Check**: Identifies if the domain accepts all emails, even if the specific address doesn't exist.
- **Disposable Email Detection**: Flags temporary or disposable email addresses commonly used for short-term or spam purposes.
- **Role Address Detection**: Identifies role-based addresses (like `support@`, `info@`, etc.), often used by businesses.

## Usage

**Email Verifier** allows you to check a single email or multiple email addresses at once. Here’s how to use the tool:

### 1. **First Input Field**: **Valid Email Address**
- Enter a valid email address in the first field. This is needed because some email providers check for **real connections** before accepting emails.

### 2. **Second Input Field**: **Port**
- Enter the port number for the SMTP server (the default port is **25**). You can specify any port you need to connect to the email server.

### 3. **Third Input Field**: **Email Check/Verifier**
- This field allows you to check one or multiple email addresses. You can enter a **single email** or **bulk email addresses**, separated by a newline.

### Example of Response Results:

When you check an email or multiple emails, the response will look like this:

```json
[
  {
    "email": "info@domain.com",
    "format_valid": true,
    "mx_found": true,
    "smtp_check": true,
    "catch_all": true,
    "role": true,
    "disposable": false
  },
  {
    "email": "contact@example.com",
    "format_valid": true,
    "mx_found": true,
    "smtp_check": true,
    "catch_all": true,
    "role": true,
    "disposable": false
  }
]
```

This JSON response provides details about each email's validation, including whether it is:
- **format_valid**: Whether the email is correctly formatted.
- **mx_found**: Whether the domain has valid MX records.
- **smtp_check**: Whether the email server accepts the address.
- **catch_all**: Whether the domain accepts all emails, even if the specific address doesn’t exist.
- **role**: Whether the email is a role-based address (e.g., `support@`).
- **disposable**: Whether the email address is from a disposable provider.

## Why Use Email Verifier?

By using an Email Verifier, you can ensure:
- **Better deliverability**: Send emails only to valid addresses.
- **Improved engagement**: Avoid sending to temporary or invalid emails.
- **Cleaner email lists**: Keep your email database up-to-date and reliable.
- **Reduced bounce rates**: Prevent your email campaigns from being marked as spam.

## Usage

You can use this tool to validate individual email addresses or bulk check multiple emails in one go. It's particularly useful for marketers, developers, and businesses to ensure the success of their email campaigns.

## Installation

To get started with **Email Verifier**, you can either clone the repository or download it directly.

### Option 1: Clone the Repository

1. Clone the repository to your local machine:
    ```bash
    git clone https://github.com/tuberboy/email-verifier.git
    ```

2. Navigate into the project directory:
    ```bash
    cd email-verifier
    ```

3. Start the PHP server:
    ```bash
    php -S 0.0.0.0:8000
    ```

4. Now visit the following URL in your browser:
    ```
    http://ip_OR_localhost:8000
    ```

### Option 2: Download and Upload

1. Download the repository as a ZIP file or using the "Download" button.
2. Upload the files to your PHP-enabled server.

Once uploaded, you can access it at: `http://your-domain-or-ip` or `http://your-domain-or-ip/email-verifier`



## Fields & Explanations

### 1. email  
📧 **Description:** The email address that is being verified.

---

### 2. format_valid
✅ **Description:** Checks if the email address follows the correct format based on standard email rules.  
🔍 **Example:**  
- **Valid:** `user@example.com`  
- **Invalid:** `user@@example..com`  

---

### 3. mx_found
📡 **Description:** Determines whether the email domain has valid Mail Exchange (MX) records, which are required for receiving emails.  
🔍 **Example:**  
- **✅ Yes:** `gmail.com` (MX records exist)  
- **❌ No:** `example.invalid` (No MX records found)  

---

### 4. smtp_check
📬 **Description:** Verifies if the email server actually **accepts** messages, ensuring the email is capable of receiving emails.  
🔍 **Example:**  
- **✅ Yes:** The email server responded positively.  
- **❌ No:** The email server rejected the verification request.  

---

### 5. catch_all
🎯 **Description:** Indicates if the email server is configured to **accept all emails**, even those that might not exist.  
🔍 **Example:**  
- **✅ Yes:** `anything@example.com` is accepted even if it doesn't exist.  
- **❌ No:** The server rejects unknown emails.  

---

### 6. role
🏢 **Description:** Identifies if the email belongs to a **generic role-based address** instead of an individual person.  
👥 **Why it matters?** Role-based emails are usually managed by **multiple people** and may not be personal inboxes.  
📌 **Common Role Emails:**  
- **Service addresses:** `billing@`, `contact@`, `info@`, `support@`, `techhelp@`  
- **Position addresses:** `admin@`, `ceo@`, `customercare@`, `director@`, `editors@`  

🔍 **Example:**  
- **✅ Yes:** `info@example.com` (Likely a shared inbox)  
- **❌ No:** `john.doe@example.com` (Personal email)

---

### 7. disposable
⏳ **Description:** Determines if the email is from a **temporary/disposable email provider**, often used for one-time sign-ups.  
⚠️ **Why it matters?** Disposable emails are commonly used for spam and may not be reliable.  
📌 **Common Disposable Email Providers:**  
- `mailinator.com`  
- `10minutemail.com`  
- `temp-mail.org`  

🔍 **Example:**  
- **✅ Yes:** `user@mailinator.com` (Disposable email)  
- **❌ No:** `user@gmail.com` (Permanent email)

---

## Contributing

We welcome contributions to **Email Verifier**! If you'd like to improve this tool, please follow these steps:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Make your changes and commit them (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Open a Pull Request.

Please make sure your code follows the existing style and includes tests if applicable. We appreciate your help in making **Email Verifier** better!

## Issues

Before opening a new issue, please **check existing issues** to see if your problem or request has already been reported. This helps reduce duplicates and speeds up the resolution process.

- Go to the [Issues page](https://github.com/tuberboy/email-verifier/issues) to view open issues.
- If you find an existing issue that matches yours, feel free to **comment** or **thumbs-up** it to show your support.
- If you don't find a matching issue, **create a new one** by clicking the "New Issue" button and providing as much detail as possible.

When creating a new issue, please include:
- A **clear description** of the problem or feature request.
- **Steps to reproduce** the issue (if applicable).
- Any relevant **error messages** or **logs**.

We will review your issue as soon as possible and appreciate your contribution in making **Email Verifier** better!

## Final Note

Thank you for using **Email Verifier**! This tool aims to make email validation easy and reliable. If you have any questions or feedback, feel free to open an issue in the repository. Happy coding!
