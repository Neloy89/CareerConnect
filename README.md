# CareerConnect

## About the Project

CareerConnect is a robust web-based job portal designed to bridge the gap between job seekers and companies. This platform allows companies to post jobs, manage applications, and schedule interviews, while job seekers can search for jobs, apply, and track their application status.

**Why CareerConnect?**
- Simplifies the job search and recruitment process for both job seekers and employers.
- Offers a modern, intuitive dashboard for admins, companies, and job seekers.
- Built with PHP and MySQL, with a clean, responsive frontend using HTML and CSS.

## Features

- User Authentication: 
  Secure login/register for job seekers, companies, and admins.
  Also can change password by using forget password feature.

- Admin Dashboard: 
  View analytics, manage users, job listings, and monitor platform activity.

- Company Dashboard: 
  Post jobs, view applications, schedule interviews, and manage company profile.

- Job Seeker Dashboard: 
  Search jobs, apply, manage applications, edit profile, and view interview schedules.

- Follow Companies: 
  Job seekers can follow companies to stay updated.

- File Uploads: 
  Resume, cover letter, and portfolio uploads for applications and profiles.

- Responsive UI: 
  Works well on desktops, tablets.

- Role-based Views: 
  Different interfaces and privileges for admins, companies, and job seekers.

- Filter & Search: 
  Advanced job and applicant filtering, searching by title, location, type, etc.

- Privacy & Help Center: 
  Dedicated pages for privacy policy, terms, contact, and help.

---


> Note: Replace the above `#` with your actual image URLs or local paths. 

## Getting Started

### Prerequisites

- PHP 7.4+ (or newer)
- MySQL 5.7+ (or newer)
- Apache/Nginx (recommended)
- Composer (optional, if you want to use additional PHP libraries)

### Installation

1. Clone the repository:
    ```sh
    git clone git@github.com:Neloy89/CareerConnect.git
    cd careerconnect
    ```

2. Import the Database:
    - Create a database named `careerconnect` in MySQL.
    - The tables are auto-created at runtime by the PHP scripts. (No need for a separate SQL file.)

3. Configure Database Connection:
    - Open any PHP config file if you want to change DB credentials (default: user `root`, password `''`, database `careerconnect`).
    - Update as needed in all PHP files.

4. Set File Permissions (for uploads):
    ```sh
    mkdir -p uploads/jobseeker
    chmod -R 775 uploads
    ```

5. Start the server:
    - Using Apache/Nginx, point your document root to this directory.
    - Or use PHP built-in server for demo:
        ```sh
        php -S localhost:8000
        ```

6. Access in Browser:
    - Visit: [http://localhost/Login_Try.php](http://localhost/Login_Try.php)

---

## Usage

- Register as a Job Seeker, Company, or Admin.
- Job Seekers: 
  Search and apply for jobs, upload resume, manage applications, follow companies, and edit profile.

- Companies: 
  Post jobs, view/manage applicants, schedule interviews, and update company information.

- Admins: 
  Monitor the platform, view analytics, manage users and job listings.

- Privacy, Terms, Help Center: 
  Access from the footer for platform policies and support.

---

## Folder Structure

```
careerconnect/
├── applications.php
├── applications.css
├── companycard.css
├── contact_us.php
├── create_company_follows_table.php
├── dashboard.php
├── dashboard.css
├── delete_application.php
├── edit_application.php
├── follow_company.php
├── footer.php
├── get_job_details.php
├── help_center.php
├── information.php
├── information.css
├── interviews.php
├── interviews.css
├── joblists.php
├── jscompany.php
├── jscompany.css
├── jsdash.php
├── jsdash.css
├── jsinterview.php
├── jsinterview.css
├── jsjobs.php
├── jsjobs.css
├── jsprofile.php
├── jsprofile.css
├── Login_Try.php
├── Login_Try.css
├── postjob.php
├── postjob.css
├── privacy_policy.php
├── terms_of_service.php
├── Try_Rakib.css
├── update_status.php
├── userlist.php
├── uploads/
│   └── jobseeker/
└── [assets/images]
```

## Contributing

We welcome contributions! Please open an issue or pull request for any improvements or bug fixes.

1. Fork the project.
2. Create your feature branch (`git checkout -b feature/FeatureName`).
3. Commit your changes (`git commit -m 'Add FeatureName'`).
4. Push to the branch (`git push origin feature/FeatureName`).
5. Open a pull request.

---

## License

This project is licensed under the- 
MIT License  
Copyright (c) 2025 [22-488123-3@student.aiub.edu]

---

## Contact

For support and inquiries:

- **Email:** fardinneloy89@gmail.com
- **Support:** 01799287878

---

> _Made by AIUBians for the Bangladesh developer community and job market.
