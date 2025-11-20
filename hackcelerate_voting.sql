-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 03:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hackcelerate_voting`
--

-- --------------------------------------------------------

--
-- Table structure for table `problems`
--

CREATE TABLE `problems` (
  `id` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `tier` enum('tier1','tier2','tier3') NOT NULL,
  `votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `problems`
--

INSERT INTO `problems` (`id`, `name`, `description`, `tier`, `votes`) VALUES
('2PS1', 'Seminar Hall/ Auditorium Booking System', 'Develop an application for booking seminar halls and auditoriums within a college. The system should allow students, faculty, and staff to check real-time availability of seminar rooms, lecture halls, and auditoriums, book the spaces for specific dates and times, and manage their reservations easily.', 'tier2', 0),
('2PS2', 'Budget Indent and Automation System', 'Develop a system that allows departments to create, submit, and track their yearly budget requests in an organized and automated manner. The system should record budget indents, monitor approval stages, and generate analytical reports such as department-wise summaries, yearly allocations, and expense comparisons. By automating the budget process, it reduces manual work, minimizes errors, improves transparency, and helps administrators make data-driven financial decisions efficiently.', 'tier2', 0),
('2PS3', 'Alumni Network Management Portal', 'Develop a Portal that enables institutions to build and maintain strong connections with their alumni community. The system should allow alumni to register, update profiles, share professional updates, participate in events, and network with peers. It should also streamline communication and collaboration among alumni, students, and administrators, ensuring organized data management and efficient engagement across the institution.', 'tier2', 0),
('2PS4', 'College Annual Report Generation System', 'Develop a system that allows departments to submit their yearly data in an organized way and enables admins to automatically generate the college\'s annual report. The system should reduce manual work, improve accuracy, and make report creation faster and more efficient across the campus.', 'tier2', 0),
('2PS5', 'Student-Staff Data Management System with Chatbot Interface', 'Develop a system with a Chatbot Interface to securely handle academic and administrative information. The system should allow easy access to student and staff records, assignment details, and real-time query resolution through an integrated chatbot, ensuring efficient communication and streamlined data management across the institution.', 'tier2', 0),
('2PS6', 'Hostel Gate Pass Management System', 'Develop a smart digital system that allows hostel students to apply for gate passes online and enables wardens to monitor and approve requests efficiently. When a student applies, a notification is sent to the parents for approval before the warden grants the pass. This ensures safety, transparency, and parental involvement. The system maintains real-time records of student movements, minimizes paperwork, and enhances hostel security and management efficiency.', 'tier2', 0),
('3PS1', 'Student Attendance System Using Face Recognition', 'Develop an AI-powered Student Attendance System using Face Recognition that automatically detects and recognize student face from live camera or image, mark attendance in real-time, and store it securely in a database. The system should prevent proxy attendance, ensure high accuracy, and provide a simple dashboard for faculty to view attendance records.', 'tier3', 0),
('3PS2', 'Faculty Bulk Biometric Management System', 'Develop a system that allows administrators to register, update, and manage faculty biometric data in bulk. The system should automate attendance tracking, ensure secure data storage, and provide real-time insights and reports. It aims to minimize manual work, reduce errors, and streamline biometric management across the institution.', 'tier3', 0),
('3PS3', 'Auto Database/ Excel File Summarizer', 'Develop a tool that automatically analyzes Excel or database files and generates key insights, summaries. The system should help users quickly understand large datasets without manual analysis.', 'tier3', 0),
('3PS4', 'Document Summarizer using AI/ML', 'Develop a system that automatically summarizes large volumes of text—such as articles, research papers, reports, and web content—using Artificial Intelligence and Machine Learning techniques. The system reads and analyzes lengthy documents, identifies key ideas, and generates concise summaries while preserving the essential meaning. By reducing the time required to read long text, this solution helps students, researchers, and professionals quickly grasp important information.', 'tier3', 0),
('3PS5', 'College Bus Tracking System', 'Develop a system that allows real-time tracking of college buses for students, parents, and administrators. The system should display bus location, estimated arrival time, and route information. It aims to improve student safety, reduce waiting time, and enhance communication between the transport department and passengers.', 'tier3', 0),
('3PS6', 'Teacher Appraisal & Evaluation System for Schools', 'Develop an application that streamlines the process of evaluating teachers based on multiple performance criteria such as teaching effectiveness, classroom management, student engagement, and feedback. The system should allow students, peers, and administrators to provide structured evaluations through digital forms. It should generate comprehensive reports, visualize performance trends, and identify areas for professional growth. This will make the appraisal process more transparent, data-driven, and efficient, ultimately contributing to continuous improvement in teaching quality and academic outcomes.', 'tier3', 0),
('3PS7', 'Billing Application', 'Develop a billing application that allows shopkeepers to record customer name, mode of payment, and amount in a simple table format. The system should support multiple customers being billed at the same time without interruption. It must allow entries for cash, UPI, card, or any other payment type. The application should automatically store each entry and ensure accurate totals. Participants must build a fast, user-friendly interface that handles simultaneous billing smoothly.', 'tier3', 0),
('PS1', 'Late coming Log Analysis System', 'Develop a system that records, tracks, and analyzes late-coming data of students or staff in an organized and automated manner. The system should capture daily attendance logs, identify late entries, and generate meaningful analytics—such as frequency patterns, monthly summaries, and department-wise statistics. By eliminating manual tracking, the system improves accuracy, provides timely insights to administrators, and supports decision-making through data-driven reports.', 'tier1', 1),
('PS2', 'Admission Seat Allocation based On Merit', 'Develop a system that automates the admission process by allocating seats to students based on their merit and preferences. The system should collect application data, verify eligibility, and assign seats according to predefined merit criteria and reservation rules. It should generate real-time allocation lists, manage waiting lists, and allow administrators to track seat availability. By automating the allocation process, it ensures fairness, reduces manual errors, saves time, and improves transparency in admissions management.', 'tier1', 0),
('PS3', 'Student Counselling Application', 'Develop a system that helps students share their concerns and get timely advice from counsellors through an online platform. The application should let students fill feedback forms, receive guidance tips, and access useful resources. It aims to make counselling more comfortable, confidential, and supportive for students\' overall growth and well-being.', 'tier1', 0),
('PS4', 'Profile Information Management System', 'Develop a system to manage and maintain complete student and faculty records. The system includes separate portals for students and faculty to update details, department-level access for administrators, and an HR portal with full access to download selected records in Excel format. It ensures organized, accurate, and efficient data management across the institution.', 'tier1', 0),
('PS5', 'Circular Management System', 'Develop a system that streamlines the process of sharing official circulars within an organization. Instead of distributing updates through multiple channels like WhatsApp or email, users can upload circulars directly to a centralized portal. The system allows selection of target audiences, ensuring that notifications and email alerts are automatically sent to the relevant recipients. This improves communication efficiency, reduces information loss, and enables easy search and retrieval of circulars from the portal for future reference.', 'tier1', 0),
('PS6', 'Student Refund Process Automation', 'Develop an application that automates the student refund process in educational institutions. The system will allow students to submit refund requests digitally along with necessary details and documents. Each request will be verified and approved by the respective Head of Department (HoD) before being forwarded to the Accounts Department for final processing. Once the refund is completed, the process will be automatically marked as closed. This solution should eliminate manual paperwork, ensure transparency, reduce processing delays, and enhance efficiency for both students and administrators.', 'tier1', 0);

-- --------------------------------------------------------

--
-- Table structure for table `problem_votes`
--

CREATE TABLE `problem_votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) DEFAULT NULL,
  `problem_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `problem_votes`
--

INSERT INTO `problem_votes` (`id`, `voter_id`, `problem_id`) VALUES
(1, 1, 'PS1');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `problem` text NOT NULL,
  `tier` enum('tier1','tier2','tier3') NOT NULL,
  `votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `problem`, `tier`, `votes`) VALUES
('T001', 'Innovator', 'Student Refund Process Automation ', 'tier1', 0),
('T002', 'CodeVision AI', 'Admission Seat Allocation Based on Merit', 'tier1', 0),
('T003', 'Codex Crew', 'Circular Management System ', 'tier1', 0),
('T004', 'The Glitch Clique', 'Circular Management System ', 'tier1', 0),
('T005', 'Runtime Rebels', 'Profile Information Management System', 'tier1', 0),
('T006', 'Techie Titans', 'Late Coming Log Analysis System ', 'tier1', 0),
('T007', 'Insight Minds', 'Late Coming Log Analysis System', 'tier1', 0),
('T008', '3Bit Squad', 'Student Refund Process Automation ', 'tier1', 0),
('T009', 'Averon', 'Student Counselling Application', 'tier1', 0),
('T010', 'Code for Campus', 'Faculty Bulk Biometric Management System', 'tier1', 0),
('T011', 'Tech Wizards', 'College Annual Report Generation System', 'tier2', 0),
('T012', 'Show stoppers', 'Alumni Network Management Portal', 'tier2', 1),
('T013', 'Ctrl+Girls', 'Budget Indent and Automation System', 'tier2', 0),
('T014', 'Crusaders', 'Budget Indent and Automation System', 'tier2', 0),
('T015', 'Wizz', 'Student-Staff Data Management System with Chatbot Interface', 'tier2', 0),
('T016', 'AlgoRangers', 'Student-Staff Data Management System with Chatbot Interface', 'tier2', 0),
('T017', 'TrioX', 'Seminar Hall/Auditorium Booking System', 'tier2', 2),
('T018', 'Elevate', 'Seminar Hall/ Auditorium Booking System', 'tier2', 0),
('T019', 'Underdog Coders ', 'Hostel Gate Pass Management System', 'tier2', 0),
('T020', 'Miragex', 'Hostel Gate Pass Management System', 'tier2', 0),
('T021', 'Vision2Reality', 'Teacher Appraisal & Evaluation System for Schools', 'tier3', 0),
('T022', 'Binary Beats', 'Billing Application', 'tier3', 0),
('T023', 'Aura Blossoms', 'Billing Application', 'tier3', 1),
('T024', 'She Hacks', 'College Bus Tracking System', 'tier3', 0),
('T025', 'InnoBots', 'College Bus Tracking System', 'tier3', 0),
('T026', 'Tech CodeRegal', 'Auto DB/Excel Summarizer', 'tier3', 0),
('T027', 'Technominds', 'Document Summarizer using AI/ML', 'tier3', 0),
('T028', 'Trifusion', 'Document Summarizer using AI/ML', 'tier3', 0),
('T029', 'HackRookies', 'Student Attendance System Using Face Recognition', 'tier3', 0),
('T030', 'Trinova', 'Student Attendance System Using Face Recognition', 'tier3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `year` varchar(20) NOT NULL,
  `vote_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `name`, `email`, `student_id`, `branch`, `year`, `vote_timestamp`) VALUES
(1, 'shreya harish ', 'shreyaharish84@gmail.com', '4GW23CI047', 'AIML', '3rd Year', '2025-11-18 18:01:53'),
(2, 'sinchana ', 'sdfgj@gmail.com', '46erw3', 'ECE', '2nd Year', '2025-11-18 18:04:16'),
(3, 'beena ', 'beeena84@gmail.com', 'awdfah', 'ISE', '2nd Year', '2025-11-19 08:41:23'),
(4, 'harish', 'har@gmail.om', '4fd4e667', 'AIML', '3rd Year', '2025-11-19 12:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) DEFAULT NULL,
  `team_id` varchar(10) DEFAULT NULL,
  `vote_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `voter_id`, `team_id`, `vote_timestamp`) VALUES
(1, 1, 'T012', '2025-11-18 18:01:53'),
(2, 2, 'T023', '2025-11-18 18:04:16'),
(3, 3, 'T017', '2025-11-19 08:41:23'),
(4, 4, 'T017', '2025-11-19 12:20:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `problems`
--
ALTER TABLE `problems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `problem_votes`
--
ALTER TABLE `problem_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `problem_id` (`problem_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `team_id` (`team_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `problem_votes`
--
ALTER TABLE `problem_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `problem_votes`
--
ALTER TABLE `problem_votes`
  ADD CONSTRAINT `problem_votes_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`),
  ADD CONSTRAINT `problem_votes_ibfk_2` FOREIGN KEY (`problem_id`) REFERENCES `problems` (`id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
