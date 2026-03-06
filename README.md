Scrum-based Project Management & Progress Tracking System (MVP)

📌 Project Overview
This is a web-based application designed to manage projects and track progress using the **Scrum methodology**. The project was developed as a coursework assignment to demonstrate a full-stack integration between a **Laravel** backend and a **Vue.js** frontend within an Agile environment.

🚀 Core Features (Sprint 1)
* User Authentication: Secure Login and Registration system for team members.
* Project Dashboard: A centralized view to track ongoing projects and overall status.
* Task Management: Full CRUD operations (Create, Read, Update, Delete) for managing tasks within specific projects.
* Scrum Workflow: Organized Product Backlog and tools for Sprint planning.

🛠 Tech Stack
* Backend: PHP 8.x, Laravel Framework (RESTful APIs).
* Frontend: Vue.js, JavaScript (ES6+), CSS3.
* Database: MySQL (Relational database design).
* Tools: Postman (API Testing), Git/GitHub (Version Control).
* AI-Assisted: Cursor, GitHub Copilot (Used for code refactoring and logic optimization).

📂 System Architecture
The system follows a modern decoupled architecture:
1.  Backend: Laravel serves as a robust API provider, handling business logic, data validation, and security.
2.  Frontend: Vue.js provides a reactive and seamless user interface, communicating with the backend via Axios.
3.  Database: MySQL manages complex relationships between Users, Projects, Sprints, and Tasks.

⚙️ Installation & Setup

1.  Clone the repository:
    ```bash
    git clone [https://github.com/Lidbossbil/COTS.git](https://github.com/Lidbossbil/COTS.git)
    cd COTS
    ```

2.  Backend Setup (Laravel):
    * Install dependencies: `composer install`
    * Create environment file: `cp .env.example .env`
    * Generate app key: `php artisan key:generate`
    * Configure your database in the `.env` file, then run migrations: `php artisan migrate`

3.  Frontend Setup:
    * Install npm packages: `npm install`
    * Run development server: `npm run dev`

👥 Team & Role
* Nguyen Thanh Nam - Backend Developer (API Development, Database Schema Design).
* Collaborated with 2 Frontend members for system integration.

📝 Project Status
This project is an MVP (Minimum Viable Product. It successfully demonstrated core functionalities during the first development cycle and met all academic requirements.
