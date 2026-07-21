-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 10:00 AM
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
-- Database: `study_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `language` varchar(20) DEFAULT NULL,
  `source_code` longtext DEFAULT NULL,
  `output` text DEFAULT NULL,
  `activity_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `download_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `download_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `total_fees` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `remaining_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT NULL,
  `payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `id` int(11) NOT NULL,
  `lab_title` varchar(255) NOT NULL,
  `problem_statement` text DEFAULT NULL,
  `default_code` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`id`, `lab_title`, `problem_statement`, `default_code`, `created_at`) VALUES
(1, 'Hello World in C', 'Write a program to print \"Hello, World!\"', '#include <stdio.h>\n\nint main() {\n    printf(\"Hello, World!\");\n    return 0;\n}', '2026-07-20 05:05:55');

-- --------------------------------------------------------

--
-- Table structure for table `lab_practice`
--

CREATE TABLE `lab_practice` (
  `lab_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `language` enum('C','CPP','JAVA','PYTHON','PHP','SQL') DEFAULT NULL,
  `experiment_no` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `aim` text DEFAULT NULL,
  `theory` text DEFAULT NULL,
  `algorithm` text DEFAULT NULL,
  `flowchart` varchar(255) DEFAULT NULL,
  `source_code` longtext DEFAULT NULL,
  `output_image` varchar(255) DEFAULT NULL,
  `viva_questions` text DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_records`
--

CREATE TABLE `lab_records` (
  `record_id` int(11) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `program_title` varchar(100) DEFAULT NULL,
  `source_code` longtext DEFAULT NULL,
  `output` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_records`
--

INSERT INTO `lab_records` (`record_id`, `student_name`, `language`, `program_title`, `source_code`, `output`, `submitted_at`) VALUES
(1, 'chanvir shivputra jamadar', 'cpp', 'DSA', '#include <iostream>\r\nusing namespace std;\r\n\r\nclass Palindrome {\r\n    int num, original, reverse = 0, rem;\r\n\r\npublic:\r\n    void getNumber() {\r\n        cout << \"Enter a number: \";\r\n        cin >> num;\r\n        original = num;\r\n    }\r\n\r\n    void check() {\r\n    	cout << \"Enter a number: \";\r\n		        cin >> num;\r\n		        original = num;\r\n        while(num != 0) {\r\n            rem = num % 10;\r\n            reverse = reverse * 10 + rem;\r\n            num = num / 10;\r\n        }\r\n\r\n        if(original == reverse)\r\n            cout << \"Number is Palindrome\";\r\n        else\r\n            cout << \"Number is Not Palindrome\";\r\n    }\r\n};\r\n\r\nint main() {\r\n    Palindrome p;\r\n    p.getNumber();\r\n    p.check();\r\n    return 0;\r\n}', 'Enter a number: 4\r\nEnter a number: 4\r\nNumber is Palindrome', '2026-07-08 13:42:42'),
(2, NULL, NULL, NULL, NULL, NULL, '2026-07-19 13:47:03');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`id`, `email`, `otp`, `expiry`) VALUES
(1, 'shivputrajamadar057@gmail.com', '123', '2026-07-20 19:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `portal_activity`
--

CREATE TABLE `portal_activity` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `user_role` varchar(20) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portal_activity`
--

INSERT INTO `portal_activity` (`id`, `user_name`, `user_role`, `action_type`, `message`, `created_at`) VALUES
(1, 'Chanvir shivputra jamadar', 'Staff', 'Upload', 'Uploaded new material: chapter 2 (MATH PART 2)', '2026-07-20 06:07:14'),
(2, 'Chanvir shivputra jamadar', 'Staff', 'Upload', 'Uploaded new material: ddd (MATH PART 2)', '2026-07-20 06:30:48'),
(3, 'Chanvir shivputra jamadar', 'Staff', 'Delete', 'Deleted material: ddd', '2026-07-20 07:41:15'),
(4, 'Chanvir shivputra jamadar', 'Staff', 'Delete', 'Deleted material: Unit 1 Notes', '2026-07-20 07:43:47'),
(5, 'Chanvir shivputra jamadar', 'Staff', 'Upload', 'Uploaded Question Bank: mid term question paper (MATH PART 2 - 2026)', '2026-07-20 07:51:39'),
(6, 'Chanvir shivputmadar', 'Staff', 'Upload', 'Uploaded Question Bank: jhd (ywgd - jehw)', '2026-07-20 08:36:57'),
(7, 'Chanvir  jamadar', 'Staff', 'Delete', 'Deleted material: chapter 2', '2026-07-20 09:37:43'),
(8, 'Chanvir  jamadar', 'Staff', 'Delete', 'Deleted Question Bank item: jhd', '2026-07-20 09:37:53'),
(9, 'Chanvir  jamadar', 'Staff', 'Delete', 'Deleted Question Bank item: mid term question paper', '2026-07-20 09:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `practice_questions`
--

CREATE TABLE `practice_questions` (
  `practice_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `added_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_questions`
--

INSERT INTO `practice_questions` (`practice_id`, `subject`, `language`, `difficulty`, `question`, `solution`, `added_by`, `created_at`) VALUES
(1, 'CPP', 'CPP', 'MORE', 'Q', 'S', 'MAHESH SIR', '2026-07-08 13:49:34');

-- --------------------------------------------------------

--
-- Table structure for table `previous_year_papers`
--

CREATE TABLE `previous_year_papers` (
  `paper_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `year` year(4) DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queries`
--

CREATE TABLE `queries` (
  `query_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('Pending','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank`
--

CREATE TABLE `question_bank` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_bank`
--

INSERT INTO `question_bank` (`id`, `subject`, `year`, `title`, `description`, `file_name`, `upload_date`) VALUES
(1, 'MATH PART 2', '2026', 'unit1 ', 'qb', '1784488651_Unit 3 Question bank.pdf', '2026-07-19 19:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `internal_marks` int(11) DEFAULT NULL,
  `external_marks` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profile`
--

CREATE TABLE `staff_profile` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_profile`
--

INSERT INTO `staff_profile` (`id`, `staff_id`, `name`, `email`, `password`, `phone`, `mobile_no`, `qualification`, `department`, `designation`, `gender`, `dob`, `address`, `photo`, `reset_token`, `token_expiry`) VALUES
(1, 'S001', 'Admin Staff', 'shivputrajamadar@gmail.com', '1234567', '90219243', NULL, NULL, 'IT Department', 'Senior Manager', 'Male', '1999-05-11', 'ganganagr, phurusungi, pune-412308', '1784606758_IMG_20260219_132131706.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(123, 'Yash Vinodbhai Jadhav', 'yj64003@gmail.com', '12345', '2026-07-21 06:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `prn` varchar(20) DEFAULT NULL,
  `roll_no` varchar(20) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `division` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `abc_id` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_mobile` varchar(15) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_mobile` varchar(15) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_mobile` varchar(15) DEFAULT NULL,
  `admission_year` int(11) DEFAULT NULL,
  `cgpa` decimal(4,2) DEFAULT NULL,
  `attendance` decimal(5,2) DEFAULT NULL,
  `fees_status` varchar(30) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `password`, `full_name`, `user_id`, `prn`, `roll_no`, `branch`, `semester`, `division`, `phone`, `email`, `dob`, `gender`, `blood_group`, `aadhaar_no`, `abc_id`, `address`, `city`, `state`, `pincode`, `father_name`, `father_mobile`, `mother_name`, `mother_mobile`, `guardian_name`, `guardian_mobile`, `admission_year`, `cgpa`, `attendance`, `fees_status`, `photo`) VALUES
(1, '12345', 'Yash Jadhav', 1, 'PRN20260001', '101', 'Information Technology', 6, 'A', '9876543210', 'yash@gmail.com', '2004-01-15', 'Male', 'O+', '123412341234', 'ABC123456789', 'Pune Maharashtra', 'Pune', 'Maharashtra', '411041', 'Rajesh Jadhav', '9876543201', 'Sunita Jadhav', '9876543202', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_academic`
--

CREATE TABLE `student_academic` (
  `academic_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `roll` varchar(50) DEFAULT NULL,
  `prn` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `attendance` int(11) DEFAULT NULL,
  `total_lectures` int(11) DEFAULT NULL,
  `present_lectures` int(11) DEFAULT NULL,
  `absent_lectures` int(11) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `mentor` varchar(100) DEFAULT NULL,
  `fee_status` varchar(20) DEFAULT NULL,
  `total_fees` int(11) DEFAULT NULL,
  `paid_fees` int(11) DEFAULT NULL,
  `pending_fees` int(11) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `internship_duration` varchar(50) DEFAULT NULL,
  `internship_domain` varchar(100) DEFAULT NULL,
  `internship_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_academic`
--

INSERT INTO `student_academic` (`academic_id`, `student_id`, `roll`, `prn`, `department`, `semester`, `mobile`, `attendance`, `total_lectures`, `present_lectures`, `absent_lectures`, `cgpa`, `mentor`, `fee_status`, `total_fees`, `paid_fees`, `pending_fees`, `company_name`, `internship_duration`, `internship_domain`, `internship_status`) VALUES
(0, 123, 'IT1216', '125UIT1142', 'Information Technology', '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 1, 'IT1216', '125UIT1142', 'Information Technology', 'Semester III', '9876543210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_code`
--

CREATE TABLE `student_code` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `saved_code` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `document_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `aadhaar_file` varchar(255) DEFAULT NULL,
  `pan_file` varchar(255) DEFAULT NULL,
  `ssc_marksheet` varchar(255) DEFAULT NULL,
  `hsc_marksheet` varchar(255) DEFAULT NULL,
  `diploma_marksheet` varchar(255) DEFAULT NULL,
  `leaving_certificate` varchar(255) DEFAULT NULL,
  `caste_certificate` varchar(255) DEFAULT NULL,
  `income_certificate` varchar(255) DEFAULT NULL,
  `domicile_certificate` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `profile_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `abc_id` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_mobile` varchar(15) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_mobile` varchar(15) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_mobile` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `cgpa` decimal(4,2) DEFAULT NULL,
  `attendance` decimal(5,2) DEFAULT NULL,
  `fees_status` varchar(30) DEFAULT NULL,
  `guardian_relation` varchar(50) DEFAULT NULL,
  `guardian_email` varchar(100) DEFAULT NULL,
  `guardian_occupation` varchar(100) DEFAULT NULL,
  `medical_condition` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `aadhaar_file` varchar(255) DEFAULT NULL,
  `pan_file` varchar(255) DEFAULT NULL,
  `ssc_file` varchar(255) DEFAULT NULL,
  `hsc_file` varchar(255) DEFAULT NULL,
  `lc_file` varchar(255) DEFAULT NULL,
  `caste_file` varchar(255) DEFAULT NULL,
  `income_file` varchar(255) DEFAULT NULL,
  `domicile_file` varchar(255) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `roll_no` varchar(50) DEFAULT NULL,
  `prn` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profile`
--

INSERT INTO `student_profile` (`profile_id`, `student_id`, `aadhaar_no`, `abc_id`, `dob`, `gender`, `blood_group`, `address`, `city`, `state`, `pincode`, `father_name`, `father_mobile`, `mother_name`, `mother_mobile`, `guardian_name`, `guardian_mobile`, `photo`, `cgpa`, `attendance`, `fees_status`, `guardian_relation`, `guardian_email`, `guardian_occupation`, `medical_condition`, `emergency_contact`, `aadhaar_file`, `pan_file`, `ssc_file`, `hsc_file`, `lc_file`, `caste_file`, `income_file`, `domicile_file`, `receipt_file`, `roll_no`, `prn`, `department`, `semester`, `mobile`, `father_occupation`, `mother_occupation`) VALUES
(1, 123, '913614142540', '330173569214', '2012-02-29', '', 'A+', 'PARAL,MUMBAI,MAHARSHTRA-412308\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n', 'Mumbai', 'Maharashtra', '412308', '', '', '', '', 'Vinodbhai Vasantbhai Jadhav', '7066124018', 'photo_123_1784620156.jpg', 8.75, 91.00, 'Paid', 'Father', '', 'Farmer', '', '', 'aadhaar_file_123_1784620156.jpg', 'pan_file_123_1784620156.png', 'ssc_file_123_1784620156.png', 'hsc_file_123_1784620156.jpg', NULL, NULL, NULL, NULL, NULL, '23IT001', '1234567890', 'Information Technology', 'Semester VI', '8551290543', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `student_queries`
--

CREATE TABLE `student_queries` (
  `query_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `staff_reply` text DEFAULT NULL,
  `status` enum('Pending','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_material`
--

CREATE TABLE `study_material` (
  `material_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_materials`
--

CREATE TABLE `study_materials` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_materials`
--

INSERT INTO `study_materials` (`id`, `subject`, `title`, `description`, `file_name`, `upload_date`) VALUES
(4, 'MATH PART 2', 'unit 3', 'notes ', '1784520962_unit 3 notes.pdf', '2026-07-20 04:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`, `semester`, `branch`) VALUES
(1, 'Programming in C', 'CS101', 'Semester 1', 'IT'),
(2, 'Java Programming', 'CS102', 'Semester 2', 'IT'),
(3, 'Python Programming', 'CS103', 'Semester 3', 'IT'),
(4, 'PHP Programming', 'CS104', 'Semester 4', 'IT');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus`
--

CREATE TABLE `syllabus` (
  `syllabus_id` int(11) NOT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `unit_no` int(11) DEFAULT NULL,
  `topic_name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`download_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_practice`
--
ALTER TABLE `lab_practice`
  ADD PRIMARY KEY (`lab_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `lab_records`
--
ALTER TABLE `lab_records`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `portal_activity`
--
ALTER TABLE `portal_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD PRIMARY KEY (`practice_id`);

--
-- Indexes for table `previous_year_papers`
--
ALTER TABLE `previous_year_papers`
  ADD PRIMARY KEY (`paper_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `queries`
--
ALTER TABLE `queries`
  ADD PRIMARY KEY (`query_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `question_bank`
--
ALTER TABLE `question_bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff_profile`
--
ALTER TABLE `staff_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `prn` (`prn`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_academic`
--
ALTER TABLE `student_academic`
  ADD PRIMARY KEY (`academic_id`);

--
-- Indexes for table `student_code`
--
ALTER TABLE `student_code`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_queries`
--
ALTER TABLE `student_queries`
  ADD PRIMARY KEY (`query_id`);

--
-- Indexes for table `study_material`
--
ALTER TABLE `study_material`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `syllabus`
--
ALTER TABLE `syllabus`
  ADD PRIMARY KEY (`syllabus_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_records`
--
ALTER TABLE `lab_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `portal_activity`
--
ALTER TABLE `portal_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `practice_questions`
--
ALTER TABLE `practice_questions`
  MODIFY `practice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `question_bank`
--
ALTER TABLE `question_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff_profile`
--
ALTER TABLE `staff_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `student_code`
--
ALTER TABLE `student_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_queries`
--
ALTER TABLE `student_queries`
  MODIFY `query_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `study_materials`
--
ALTER TABLE `study_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `syllabus`
--
ALTER TABLE `syllabus`
  MODIFY `syllabus_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
