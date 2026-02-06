# Hospital HR System

A comprehensive, modular PHP-based Human Resources Management System for hospitals, featuring employee management, payroll processing, compensation management, HMO administration, and analytics - all with RESTful APIs and microservices architecture.

## 🏥 System Features

### Core Modules

#### 1. **Authentication & Security**
- JWT token-based authentication
- User registration and login
- Password hashing (BCRYPT)
- Role-based access control (Admin, HR, Manager, Finance, Employee)
- Token refresh and verification

#### 2. **HR Core Module**
- Employee profile management
- Job information tracking
- Department hierarchy
- Employee documents storage
- Organizational structure
- Employee search and filtering

#### 3. **Payroll Module**
- Salary configuration and management
- Payslip generation
- Tax and contribution calculations (BIR, SSS, PhilHealth, Pag-IBIG)
- Earnings and deductions management
- Payroll processing and approval
- Salary history tracking

#### 4. **Compensation Module**
- Salary grades and bands
- Allowances and benefits management
- Incentives and bonuses
- Salary adjustment workflows
- Compensation history

#### 5. **HMO Module**
- Health insurance provider management
- Plan enrollment and management
- Dependent management
- Claim processing and tracking
- Premium reconciliation

#### 6. **Analytics Module**
- Dashboard with KPIs
- Employee metrics
- Payroll analytics
- Custom reporting
- Data export capabilities

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **API**: RESTful API with JSON
- **Authentication**: JWT (JSON Web Tokens)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: Microservices

## 📁 Project Structure

```
public_html/
├── config/                          # Core configuration
│   ├── Database.php                # Database connection
│   ├── Auth.php                    # JWT authentication
│   ├── Response.php                # API response handler
│   ├── Request.php                 # Request handler
│   ├── BaseModel.php               # Base model class
│   └── BaseController.php          # Base controller class
├── modules/                         # Microservice modules
│   ├── auth/
│   │   ├── models/User.php
│   │   └── controllers/AuthController.php
│   ├── hr_core/
│   │   ├── models/Employee.php
│   │   ├── models/Department.php
│   │   └── controllers/HRCoreController.php
│   ├── payroll/
│   │   ├── models/PayrollModels.php
│   │   └── controllers/PayrollController.php
│   ├── compensation/
│   ├── hmo/
│   └── analytics/
├── database/
│   └── schema.sql                  # Database schema
├── assets/
│   └── css/
│       ├── auth.css                # Login page styles
│       └── dashboard.css           # Dashboard styles
├── index.php                       # Login page
├── dashboard.php                   # Main dashboard
├── API_DOCUMENTATION.md            # API reference
└── SETUP_GUIDE.md                 # Installation guide
```

## 🚀 Quick Start

### Prerequisites
- XAMPP (PHP 7.4+ with MySQL)
- Web Browser

### Installation

1. **Extract Files**
   ```bash
   Extract to: C:\NEWXAMPP\htdocs\public_html\
   ```

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

3. **Create Database**
   - Go to: http://localhost/phpmyadmin
   - Create database: `hospital_hr_system`
   - Import: `database/schema.sql`

4. **Access System**
   - Open: http://localhost/public_html/
   - Login with credentials below

### Default Credentials

```
Email: admin@hospital.com
Password: password

Email: hr@hospital.com
Password: password

Email: employee@hospital.com
Password: password
```

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth.php?action=login
POST   /api/auth.php?action=register
POST   /api/auth.php?action=verify
POST   /api/auth.php?action=refresh
```

### HR Core
```
GET    /modules/hr_core/controllers/HRCoreController.php?action=getEmployees
GET    /modules/hr_core/controllers/HRCoreController.php?action=getEmployee&id=1
POST   /modules/hr_core/controllers/HRCoreController.php?action=createEmployee
PUT    /modules/hr_core/controllers/HRCoreController.php?action=updateEmployee&id=1
DELETE /modules/hr_core/controllers/HRCoreController.php?action=deleteEmployee&id=1
GET    /modules/hr_core/controllers/HRCoreController.php?action=getDepartments
```

### Payroll
```
GET    /modules/payroll/controllers/PayrollController.php?action=getEmployeeSalary&employee_id=1
GET    /modules/payroll/controllers/PayrollController.php?action=getEmployeePayslips&employee_id=1
POST   /modules/payroll/controllers/PayrollController.php?action=generatePayslip
PUT    /modules/payroll/controllers/PayrollController.php?action=approvePayslip&id=1
GET    /modules/payroll/controllers/PayrollController.php?action=getPayrollSummary
```

## 📚 Documentation

- **API Documentation**: See [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Setup Guide**: See [SETUP_GUIDE.md](SETUP_GUIDE.md)

## 🔐 Security Features

- JWT token authentication with expiration
- BCRYPT password hashing
- SQL injection prevention (prepared statements)
- Role-based access control
- CORS support
- Request validation
- Audit logging

## 💡 Key Features

✅ Complete employee lifecycle management
✅ Automated payroll processing
✅ Real-time dashboard analytics
✅ Role-based permissions
✅ RESTful API architecture
✅ Responsive design
✅ Data export capabilities
✅ Audit trail and compliance
✅ HMO/Insurance management
✅ Leave and attendance tracking

## 🔧 Configuration

### Database Configuration
Edit `config/Database.php`:
```php
private $host = 'localhost';
private $db_name = 'hospital_hr_system';
private $username = 'root';
private $password = '';
```

### JWT Secret Key
Edit `config/Auth.php`:
```php
private $secret_key = 'hospital_hr_system_secret_key_2025';
```

## 📊 Database Schema

### Main Tables
- `users` - User accounts and authentication
- `employees` - Employee master records
- `job_information` - Position and employment details
- `departments` - Organizational departments
- `positions` - Job positions
- `employee_salary` - Salary information
- `payslips` - Generated payslips
- `earnings` - Employee earnings
- `deductions` - Employee deductions
- `tax_contributions` - Tax and benefit deductions
- `hmo_providers` - Health insurance providers
- `hmo_plans` - HMO plan definitions
- `hmo_enrollment` - Employee HMO enrollment
- `hmo_claims` - Insurance claims
- `attendance` - Attendance records
- `leaves` - Leave requests
- `audit_logs` - System audit trail

## 🌐 API Usage Examples

### Login
```bash
curl -X POST http://localhost/public_html/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@hospital.com","password":"password"}'
```

### Get Employees
```bash
curl -X GET "http://localhost/public_html/modules/hr_core/controllers/HRCoreController.php?action=getEmployees" \
  -H "Authorization: Bearer <token>"
```

### Create Employee
```bash
curl -X POST http://localhost/public_html/modules/hr_core/controllers/HRCoreController.php?action=createEmployee \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"employee_code":"EMP123","first_name":"John","last_name":"Doe","email":"john@hospital.com"}'
```

## 📈 Dashboard Features

- **KPI Cards**: Total employees, new hires, on leave, pending approvals
- **Charts**: Employee distribution, payroll summary
- **Quick Actions**: Add employee, run payroll, manage leaves, view reports
- **Recent Activities**: System activity log
- **Navigation**: Quick access to all modules

## ✨ Responsive Design

- Mobile-friendly interface
- Tablet optimization
- Desktop support
- Touch-friendly controls
- CSS Grid and Flexbox layout

## 🧪 Testing

### Test User Accounts
```
Admin:    admin@hospital.com / password
HR:       hr@hospital.com / password
Employee: employee@hospital.com / password
```

### API Testing
Use tools like:
- Postman
- cURL
- Insomnia
- Thunder Client

## 🔄 Module Integration

Each module operates independently but can communicate via:
- Shared database
- REST API calls
- Event-based triggers
- Data synchronization

## 📝 Audit & Compliance

- All changes logged in `audit_logs` table
- User action tracking
- Timestamp recording
- Change history

## 🚨 Error Handling

Standardized error responses:
```json
{
  "status": 400,
  "message": "Error description",
  "data": {"field": "error details"},
  "timestamp": "2025-02-04 10:30:00"
}
```

## 📦 Deployment

### Requirements
- PHP 7.4+ with MySQL
- Apache with mod_rewrite
- 500MB+ disk space
- HTTPS recommended for production

### Production Checklist
- [ ] Change JWT secret key
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Configure backups
- [ ] Set up monitoring
- [ ] Create admin accounts
- [ ] Test all APIs
- [ ] Train users

## 🤝 Contributing

To add new modules:
1. Create module folder structure
2. Create models extending `BaseModel`
3. Create controllers extending `BaseController`
4. Create views with responsive design
5. Update API documentation
6. Test thoroughly

## 📄 License

© 2025 Hospital HR System. All rights reserved.

## 📞 Support

For issues or questions:
1. Check API_DOCUMENTATION.md
2. Review SETUP_GUIDE.md
3. Check database logs
4. Review error messages

## 🗓 Version History

### v1.0.0 (February 4, 2025)
- Initial release
- Core modules implemented
- JWT authentication
- Dashboard and modules
- API endpoints
- Complete documentation

## 🎯 Roadmap

- [ ] Advanced reporting with charts
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] API rate limiting
- [ ] Swagger/OpenAPI documentation
- [ ] Unit tests
- [ ] Performance optimization
- [ ] Mobile app

---

**System Name**: Hospital HR Management System  
**Version**: 1.0.0  
**Last Updated**: February 4, 2025  
**Status**: Active Development
