-- UIU Club Management System (CMS) Database Seeders
USE `cms`;

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'superadmin', 'Super Administrator with full access across all UIU clubs'),
(2, 'faculty_advisor', 'Faculty Advisor with budget approval & club oversight authority'),
(3, 'club_executive', 'Club Executive Member / President / VP with event & member management access'),
(4, 'student', 'General Student Member of UIU');

-- 2. Clubs
INSERT INTO `clubs` (`id`, `code`, `name`, `motto`, `description`, `established_year`, `status`) VALUES
(1, 'computer_club', 'UIU Computer Club', 'Innovate, Code, Elevate', 'The official tech club of United International University organizing competitive programming, hackathons, and software workshops.', 2012, 'active'),
(2, 'robotics_club', 'UIU Robotics Club', 'Building the Future with Robotics', 'Focuses on robotics engineering, IoT projects, rover competitions, and hardware innovation at UIU.', 2014, 'active'),
(3, 'social_service_club', 'UIU Social Service Club', 'Serving Humanity with Passion', 'Dedicated to blood donation drives, charity campaigns, community welfare, and emergency relief.', 2013, 'active'),
(4, 'app_forum', 'UIU App Forum', 'Connect, Share, Empower', 'The student forum for project collaborations, software showcases, and community tech discussions.', 2016, 'active');

-- 3. Users
INSERT INTO `users` (`id`, `student_id`, `name`, `email`, `password_hash`, `role_id`, `department`, `phone`) VALUES
(1, '011211001', 'System Administrator', 'admin@cms.uiu.ac.bd', '$2y$10$wO3nE.G3R5F9B0oW1LhZe.YyA6Z7p8q9r0s1t2u3v4w5x6y7z8a9b', 1, 'CSE', '+8801700000001'),
(2, '011211002', 'Dr. Salekul Islam', 'advisor@cse.uiu.ac.bd', '$2y$10$wO3nE.G3R5F9B0oW1LhZe.YyA6Z7p8q9r0s1t2u3v4w5x6y7z8a9b', 2, 'CSE', '+8801700000002'),
(3, '011221045', 'Computer Club Executive', 'exec.cc@uiu.ac.bd', '$2y$10$wO3nE.G3R5F9B0oW1LhZe.YyA6Z7p8q9r0s1t2u3v4w5x6y7z8a9b', 3, 'CSE', '+8801711111111'),
(4, '011221099', 'Robotics Club Executive', 'exec.robotics@uiu.ac.bd', '$2y$10$wO3nE.G3R5F9B0oW1LhZe.YyA6Z7p8q9r0s1t2u3v4w5x6y7z8a9b', 3, 'EEE', '+8801722222222'),
(5, '011231012', 'General Student User', 'student@bscse.uiu.ac.bd', '$2y$10$e8V9B0oW1LhZe.YyA6Z7p8q9r0s1t2u3v4w5x6y7z8a9b0c1d2e3f', 4, 'CSE', '+8801733333333');

-- 4. Club Memberships
INSERT INTO `club_memberships` (`user_id`, `club_id`, `position`, `status`) VALUES
(3, 1, 'President', 'active'),
(4, 2, 'Vice President', 'active'),
(5, 1, 'General Member', 'active'),
(5, 3, 'Volunteer', 'active');

-- 5. Events
INSERT INTO `events` (`id`, `club_id`, `title`, `description`, `event_date`, `event_time`, `location`, `type`, `status`) VALUES
(1, 1, 'UIU Inter-University Hackathon 2026', 'Annual 36-hour competitive hackathon bringing top student programmers across Bangladesh.', '2026-09-15', '09:00:00', 'UIU Multipurpose Hall', 'Hackathon', 'upcoming'),
(2, 1, 'Web Development Bootcamp', 'Comprehensive 3-day hands-on workshop covering React, Tailwind CSS, and RESTful APIs.', '2026-08-20', '14:00:00', 'Lab 4, UIU Campus', 'Workshop', 'upcoming'),
(3, 2, 'Autonomous Rover Exhibition', 'Demonstration of student-built Mars rovers and IoT autonomous drones.', '2026-08-25', '10:00:00', 'UIU Plaza', 'Exhibition', 'upcoming'),
(4, 3, 'Annual Blood Donation Drive', 'Campus-wide voluntary blood donation campaign in collaboration with Quantum Foundation.', '2026-08-18', '09:30:00', 'UIU Auditorium', 'Charity', 'upcoming');

-- 6. Budgets & Sponsorships
INSERT INTO `budgets` (`id`, `club_id`, `title`, `description`, `requested_amount`, `approved_amount`, `status`, `requested_by`, `reviewed_by`, `review_remarks`) VALUES
(1, 1, 'Hackathon 2026 Execution Budget', 'Funding for prize pool, catering, hardware kits, and guest judge honorarium.', 50000.00, 45000.00, 'approved', 3, 2, 'Approved with adjusted catering budget.'),
(2, 2, 'Rover Chassis Hardware Acquisition', 'Purchase of high-torque stepper motors and LiDAR sensors.', 30000.00, 0.00, 'pending', 4, NULL, NULL);

-- 7. Blood Donors
INSERT INTO `donors` (`full_name`, `email`, `contact_number`, `blood_group`, `permanent_address`, `gender`) VALUES
('Rahim Ahmed', 'rahim@gmail.com', '01712345678', 'A+', 'Dhanmondi, Dhaka', 'Male'),
('Fatima Khan', 'fatima@gmail.com', '01887654321', 'O+', 'Gulshan, Dhaka', 'Female'),
('Tanvir Hossain', 'tanvir@gmail.com', '01911223344', 'B+', 'Badda, Dhaka', 'Male');

-- 8. Elections & Candidates
INSERT INTO `elections` (`id`, `club_id`, `title`, `description`, `start_date`, `end_date`, `status`) VALUES
(1, 1, 'UIU Computer Club Executive Election 2026', 'Annual digital ballot to elect the President and Vice President for the upcoming academic session.', '2026-08-01', '2026-08-30', 'active');

INSERT INTO `election_candidates` (`id`, `election_id`, `user_id`, `position`, `manifesto`, `votes_count`) VALUES
(1, 1, 3, 'President', 'Pledging 5 competitive programming bootcamps and industry tech talks with Silicon Valley engineers.', 42),
(2, 1, 5, 'Vice President', 'Focusing on open-source workshops, hackathon sponsorships, and dedicated peer mentoring.', 38);

-- 9. Facilities & Bookings
INSERT INTO `facilities` (`id`, `name`, `type`, `capacity`, `location`, `status`) VALUES
(1, 'UIU Auditorium', 'venue', 500, 'Main Building, 1st Floor', 'available'),
(2, 'Multipurpose Hall', 'venue', 300, 'Annex Building', 'available'),
(3, 'Lab 4 (Computer Science)', 'venue', 60, 'Room 412, Academic Building', 'available'),
(4, 'High-Power Sound System & Mics', 'equipment', 10, 'DSA Store Room', 'available');

INSERT INTO `facility_bookings` (`facility_id`, `club_id`, `requested_by`, `booking_date`, `start_time`, `end_time`, `purpose`, `status`) VALUES
(1, 1, 3, '2026-09-15', '08:00:00', '18:00:00', 'UIU Inter-University Hackathon Opening & Closing Ceremony', 'approved'),
(3, 1, 3, '2026-08-20', '14:00:00', '17:00:00', 'Web Development Bootcamp Lab Session', 'approved');

-- 10. Announcements
INSERT INTO `announcements` (`id`, `club_id`, `title`, `content`, `priority`, `pinned`) VALUES
(1, 1, '🚨 Hackathon 2026 Registration is Now Open!', 'Assemble your teams of 3 and register before August 30. Exciting cash prizes worth ৳1,00,000!', 'urgent', 1),
(2, 2, 'Robotics Workshop Hardware Kits Distribution', 'Collect your Arduino & Sensor starter kits from Room 305 starting tomorrow at 2 PM.', 'normal', 1);

-- 11. Alumni Profiles
INSERT INTO `alumni_profiles` (`id`, `name`, `graduation_year`, `former_position`, `current_company`, `current_role`, `linkedin_url`, `email`) VALUES
(1, 'Sabbir Hossain', 2022, 'Former UIUCC President', 'Brain Station 23', 'Senior Software Engineer', 'https://linkedin.com', 'sabbir@brainstation-23.com'),
(2, 'Nusrat Jahan', 2021, 'Former UIURC VP', 'Therap BD', 'Lead Software QA Engineer', 'https://linkedin.com', 'nusrat@therapbd.com'),
(3, 'Arifur Rahman', 2020, 'Former App Forum Lead', 'Google', 'Software Engineer', 'https://linkedin.com', 'arifur@google.com');
