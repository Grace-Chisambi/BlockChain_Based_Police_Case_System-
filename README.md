# BlockChain_Based_Police_Case_System-
The Malawi Regional Police Case Management System is a web-based application designed to digitalize and secure police operations.   It replaces paper-based records with a transparent, blockchain-integrated platform that ensures complaints, cases, evidence, and assignments are securely stored and tamper-proof.

# Blockchain-Based Malawi Regional Police Case Management System

## Overview
The **Malawi Regional Police Case Management System (PRCMS)** is a web application developed to digitalize and secure police operations in Malawi.  
Traditionally, case files were stored in paper-based systems, which posed risks of theft, tampering, and lack of accountability.  
This project solves those issues by integrating **blockchain technology** to ensure that all key police case records are **tamper-proof, traceable, and auditable**.  

The system provides role-based access for **Admins, Station Officers, Supervisors, Investigators, and Prosecutors**.  
Every major activity in the case lifecycle (complaint registration, case creation, evidence upload, assignments, and closure) is recorded both **off-chain in MySQL** and **on-chain in Ethereum (Ganache testnet)** for transparency.  

## Technologies Used
- **Laravel 10 (PHP)**: Backend framework for business logic and database operations.  
- **MySQL**: Relational database for off-chain data storage.  
- **Blade Templates (Laravel)**: Frontend templating system for UI.  
- **Bootstrap 5 / TailwindCSS**: For responsive UI design.  
- **Node.js + Express**: Blockchain microservice API.  
- **Web3.js**: Library for blockchain interaction.  
- **Truffle + Ganache**: For compiling and deploying Solidity smart contracts.  
- **Solidity**: Programming language for smart contracts.  

## Project Structure
- **app/**: Laravel models and controllers for handling business logic.  
- **resources/views/**: Blade templates for the frontend UI.  
- **database/**: Migrations and seeders for database setup.  
- **blockchain-service/**: Node.js microservice for interacting with the Ethereum blockchain.  
- **contracts/**: Solidity smart contracts for case transactions, assignments, evidence, and closure.  
- **routes/web.php**: Web routes definition for Laravel.  

## Features Implemented

### General Features
1. **User Authentication & Roles**: Admin, Station Officer, Supervisor, Investigator, Prosecutor.  
2. **Complaint Registration**: Citizens’ complaints are recorded into the system.  
3. **Convert Complaints into Cases**: Station Officers/Admins can formalize a complaint into a case.  
4. **Case Assignment**: Supervisors allocate cases to investigators within their departments.  
5. **Evidence Upload**: Investigators can upload evidence with blockchain hash logging.  
6. **Case Closure**: Cases can be closed permanently, temporarily, or withdrawn, with hashes stored on blockchain.  
7. **Audit Trail**: Admin can view blockchain logs for all case activities.  

### Blockchain-Specific Features
- **Immutable Logging** of case creation, evidence, assignments, and closures.  
- **Tamper-Proof Records** stored on Ethereum blockchain.  
- **Node.js Microservice** handles automatic transaction signing (no MetaMask pop-ups for users).  

## Key Methods and Approaches

### Blockchain Microservice
- A standalone **Node.js + Express service** signs and submits blockchain transactions automatically using Ganache accounts.  
- Laravel communicates with this service via HTTP requests ( `Http::post`).  

### Laravel Backend
- Handles user roles, complaint registration, case management, and integrates with the blockchain microservice.  
- Generates SHA-256 hashes of sensitive data (case numbers, evidence, closures) before sending to blockchain.  

### Database
- Stores off-chain copies of complaints, cases, evidence, and users.  
- Blockchain transaction hashes are stored alongside records for auditing.  

## Database Design
Key tables:
- **users** – Admin, Station Officer, Supervisor, Investigator, Prosecutor.  
- **complaints** – Stores complaint statements.  
- **cases** – Converted cases with status and type.  
- **evidence** – Evidence linked to cases.  
- **assignments** – Supervisor → Investigator allocations.  
- **logs** – Audit logs for all activities.  

## Conclusion
This project demonstrates the **integration of Laravel, Node.js, MySQL, and Blockchain** to build a secure police case management system.  
It ensures **data integrity, transparency, and accountability** in law enforcement processes.  

The system successfully reduces reliance on paper files and introduces a **modern, tamper-proof case management approach** suitable for regional police stations. 

## How to Run the Project

1. **Clone the Repository**  
   ```bash
   git clone https://github.com/yourusername/police-case-management.git
2. **Create a .env file in the Laravel root:**
     APP_NAME=PoliceCMS
     APP_ENV=local
     APP_KEY=base64:generate_with_php_artisan_key_generate
     APP_DEBUG=true
     APP_URL=http://localhost:8000

     DB_CONNECTION=mysql
     DB_DATABASE=pcmsdb
     DB_USERNAME=root
     DB_PASSWORD=
3. **Run Database Migrations**
   ```bash
   php artisan migrate
4. **Install Node.js Blockchain Service**
    ```bash
   cd blockchain-service
   npm install
5. **Create a .env inside blockchain-service/:**
   RPC_URL=http://127.0.0.1:7545
   PRIVATE_KEY=0xf190b9ecf50a8f99b4c2e6127afe8036efb9b83dfeda9ada23c3246c748687a1
   WALLET_ADDRESS=0x51C0aE3B3BA8161E645A294092BB4747e474a181
   CONTRACT_ADDRESS=0xB3136902673fD4230Bd0Fd34d416d42Cc473b66a
   LARAVEL_API_URL=http://127.0.0.1:8000
   APP_DEBUG=true
6. **Run the Blockchain Service**
     ```bash
   node index.js
  **Expected output:**
  Blockchain service running on http://localhost:3001
7. **Start Laravel Application and Navihate Navigate http://127.0.0.1:8000**
   ```bash
   php artisan serve

8. System Passwords 
Admin  
Username – girey@gmail.com 
Password – gigigigi 
Supervisor 
Username – menyeyt@gmail.com 
Password – memememe 
Investigator  
Username – yamikanisuwedi@gmail.com 
Password – sasasasa 
Prosecutor  
Username – samk@gmail.com 
Password – mamamama



