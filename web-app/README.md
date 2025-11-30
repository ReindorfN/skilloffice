# SkillOffice

**Connecting customers with skilled artisans across Ghana**

SkillOffice is a web-based platform designed to formalize and streamline blue-collar job services in Ghana. The platform makes it easy for customers to find, book, and pay for services from skilled artisans, while helping artisans manage their business, track earnings, and grow their client base.

---

## 🌟 What is SkillOffice?

SkillOffice is a marketplace that bridges the gap between customers who need services and skilled artisans who provide them. Whether you need plumbing, electrical work, carpentry, cleaning, or any other skilled service, SkillOffice helps you find qualified professionals in your area.

### For Customers
- **Search & Discover**: Find skilled artisans by location, service type, or ratings
- **Easy Booking**: Request services with detailed descriptions and preferred schedules
- **Secure Payments**: Pay for services safely using Paystack integration
- **Real-time Chat**: Communicate directly with artisans about job details
- **Reviews & Ratings**: Rate and review services to help others make informed decisions
- **Track Bookings**: Monitor your service requests from pending to completion

### For Artisans/Vendors
- **Showcase Skills**: Create a professional profile highlighting your expertise
- **Manage Jobs**: Receive, accept, and track job requests from customers
- **Earnings Dashboard**: Monitor your income, payment history, and performance metrics
- **Service Listings**: Create and manage your service offerings
- **Customer Communication**: Chat with customers to discuss job requirements
- **Build Reputation**: Earn ratings and reviews to attract more customers

---

## 🎯 Key Features

### 🔐 User Authentication
- Secure registration and login system
- Role-based access (Customer or Artisan)
- Profile management for both user types

### 🔍 Smart Search
- Search artisans by business name, skills, or service categories
- Search services by category, title, or description
- Location-based results prioritization
- Quick filter tags for common services

### 📋 Booking System
- Complete booking workflow from request to completion
- Job status tracking (Pending → In Progress → Completed)
- Quote submission by artisans
- Payment integration with Paystack
- Two-step completion confirmation (vendor marks complete, customer confirms)

### 💬 Real-time Messaging
- In-app chat between customers and artisans
- Real-time message delivery using Firebase
- Conversation history
- Vendor selection interface

### 💰 Payment Processing
- Secure payment gateway integration (Paystack)
- Payment tracking and history
- Earnings management for artisans
- Payment status updates

### ⭐ Review & Rating System
- Mandatory reviews when customers confirm job completion
- 5-star rating system
- Written feedback and comments
- Average rating calculation for artisans
- Review display on vendor profiles and service pages

### 📊 Analytics & Insights
- Vendor dashboard with earnings summary
- Performance metrics (active jobs, completion rate, ratings)
- Payment history and transaction records
- Monthly earnings tracking

---

## 🏗️ How It's Built

SkillOffice is built using a modern web development approach:

- **Frontend**: HTML, CSS, and JavaScript for the user interface
- **Backend**: PHP for server-side logic and data processing
- **Database**: Firebase Firestore for storing all application data
- **Authentication**: Firebase Authentication for secure user management
- **Architecture**: MVC (Model-View-Controller) pattern for organized code structure

### Project Structure

The application is organized into clear sections:

```
web-app/
├── app/
│   ├── config/          # Application settings and routes
│   ├── controllers/     # Handles user requests and business logic
│   ├── models/          # Data structures (User, Booking, Service, etc.)
│   ├── services/        # Connects to Firebase and handles data operations
│   └── views/           # User interface templates (HTML/PHP)
├── public/              # Publicly accessible files
│   ├── css/            # Stylesheets
│   └── js/             # JavaScript files
└── index.php           # Main entry point
```

This structure ensures the code is organized, maintainable, and easy to understand.

---

## 📱 User Experience

### Customer Journey

1. **Sign Up** → Choose "Customer" role
2. **Search** → Find artisans or services
3. **View Profile** → Check ratings, reviews, and services
4. **Book Service** → Submit a service request with details
5. **Chat** → Communicate with the artisan
6. **Pay** → Make payment when job is in progress
7. **Review** → Rate and review after completion

### Artisan Journey

1. **Sign Up** → Choose "Artisan" role
2. **Create Profile** → Add business info, skills, and location
3. **List Services** → Create service offerings with pricing
4. **Receive Requests** → View and accept job requests
5. **Quote & Start** → Provide quotes and begin work
6. **Track Earnings** → Monitor payments and income
7. **Build Reputation** → Earn ratings and reviews

---

## 🔒 Security & Privacy

- All user data is securely stored in Firebase
- Passwords are encrypted and never stored in plain text
- Payment information is processed securely through Paystack
- User sessions are managed securely
- HTTPS is recommended for production deployment

---

## 💡 Key Technologies

- **PHP**: Server-side programming language
- **Firebase**: Cloud database and authentication service
- **Paystack**: Payment processing gateway
- **HTML/CSS/JavaScript**: Frontend technologies
- **Apache**: Web server with URL rewriting

---

## 📝 Important Notes

- This application uses Firebase REST API for data operations
- For production, ensure error display is disabled
- Use HTTPS for secure connections
- Regularly backup your Firestore database
- Keep your Firebase and Paystack credentials secure

---

## 🎓 Project Purpose

SkillOffice was created to formalize blue-collar job services in Ghana, making it easier for skilled artisans to find work and for customers to access quality services. The platform promotes transparency, accountability, and quality improvement within the service industry.
