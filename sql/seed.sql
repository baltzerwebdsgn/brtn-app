-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jul 17, 2026 at 08:27 PM
-- Server version: 8.0.46
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cleaning_db`
--

--
-- Dumping data for table `task_library`
--

INSERT INTO `task_library` (`id`, `name`, `frequency`, `room`, `instructions`, `hidden_time`, `conditional_time`, `total_time`, `day_of_week`, `week_of_month`) VALUES
(1, 'Wipe Counters', 'Daily', 'Kitchen', 'Use all purpose cleaner to clean the surface.', 10, 0, 10, NULL, NULL),
(2, 'Wash Dishes', 'Daily', 'Kitchen', 'Empty the sink of dirty dishes.', 30, 0, 30, NULL, NULL),
(3, 'Wipe Dinning Table', 'Daily', 'Livingroom', 'Use all purpose cleaner to clean the surface.', 2, 0, 2, NULL, NULL),
(4, 'Sanitize Sink', 'Weekly', 'Kitchen', 'Use a mild abrasive cleaner or baking soda paste. Rinse well and dry to prevent water spots.', 15, 0, 15, 'Monday', NULL),
(5, 'Sweep', 'Daily', 'Kitchen', 'Sweep or vacuum to remove loose debris.', 30, 0, 10, NULL, NULL),
(6, 'Clean Stovetop and Microwave', 'Weekly', 'Kitchen', 'Use a heavy-duty degreaser. Scrub around burners. Wipe clean with a damp sponge. Heat a bowl of water with lemon slices for 3 minutes to steam. Wipe down the interior walls and turntable.', 30, 0, 30, 'Monday', NULL),
(7, 'Mop', 'Weekly', 'Kitchen', 'Mop in sections and let air dry.', 10, 0, 10, 'Monday,Wednesday', NULL),
(8, 'Sweep and Mop', 'Weekly', 'Bedroom', 'Sweep or vacuum first to remove loose debris. Mop in sections and let air dry.', 15, 0, 15, 'Tuesday', NULL),
(9, 'Reset Room and Wipe Surfaces', 'Weekly', 'Bedroom', 'Restore room to desired organizational status and use all purpose cleaner and microfiber cloth to clean the surface.', 10, 0, 10, 'Tuesday', NULL),
(10, 'Reset Weekly Whiteboard', 'Weekly', 'Hallway', 'Erase information displayed and repopulate with the upcoming information.', 10, 0, 10, 'Sunday', NULL),
(11, 'Wipe Surfaces', 'Weekly', 'Bathroom', 'Use all purpose cleaner to clean the surface.', 15, 0, 15, 'Friday', NULL),
(12, 'Wipe Surfaces', 'Weekly', 'Livingroom', 'Use all purpose cleaner to clean the surface.', 20, 0, 20, 'Friday', NULL),
(13, 'Clothes Laundry', 'Weekly', 'Bedroom', 'Run as many laundry cycles needed to empty out the clothes in the hamper.', 120, 1, 20, 'Wednesday', NULL),
(14, 'Take out Trash and Recycling Cans', 'Weekly', 'Outside', 'Take cans out to the street.', 10, 0, 10, 'Thursday', NULL),
(15, 'Sweep and Mop', 'Weekly', 'Livingroom', 'Sweep or vacuum first to remove loose debris. Mop in sections and let air dry.', 30, 0, 30, 'Wednesday,Friday', NULL),
(16, 'Empty Trash', 'Weekly', 'Kitchen', 'Collect all bins. Wipe the bottom of the cans if leaked. Replace with fresh liners.', 5, 0, 5, 'Thursday', NULL),
(17, 'Empty Trash', 'Weekly', 'Bathroom', 'Collect all bins. Wipe the bottom of the cans if leaked. Replace with fresh liners.', 5, 0, 5, 'Thursday', NULL),
(18, 'Tidy Pantry', 'Weekly', 'Kitchen', 'Restore any items out of desired place.', 30, 0, 30, 'Thursday', NULL),
(21, 'Sweep and Mop', 'Weekly', 'Hallway', 'Sweep or vacuum first to remove loose debris. Mop in sections and let air dry.', 5, 0, 5, 'Tuesday', NULL),
(22, 'Sweep and Mop', 'Weekly', 'Bathroom', 'Sweep or vacuum first to remove loose debris. Mop in sections and let air dry.', 15, 0, 15, 'Monday', NULL),
(24, 'Clean Laundry Appliances', 'Monthly', 'Livingroom', 'Disinfect the appliances while being sure to insect all rubber linings for mold.', 30, 0, 30, NULL, '3'),
(25, 'Towels and Rags Laundry', 'Weekly', 'Bedroom', 'Run a laundry cycle on personal shower towels, hand towels, and any other cleaning rags.', 240, 1, 20, 'Friday', NULL),
(26, 'Change Hand Towels', 'Weekly', 'Bathroom', 'Replace hand towels with fresh ones.', 5, 0, 5, 'Friday', NULL),
(27, 'Change Hand Towels', 'Weekly', 'Kitchen', 'Replace hand towels with fresh ones.', 5, 0, 5, 'Friday', NULL),
(28, 'Bed Sheets and Couch Blankets Laundry', 'Weekly', 'Bedroom', 'Strip and change bed sheet.  Combine sheets with any blankets for the couch when running a laundry cycle.', 240, 1, 20, 'Tuesday', NULL),
(29, 'Clean Vehicle', 'Weekly', 'Outside', 'Remove any trash, fill up on as in needed, wipe down mirrors and glass.', 45, 0, 45, 'Saturday', NULL),
(30, 'Plan Meals', 'Weekly', 'Kitchen', 'Strategize desired meals and snacks for the week. Generate a list of ingredients for grocery shopping.', 45, 0, 45, 'Saturday', NULL),
(31, 'Meal Prep', 'Weekly', 'Kitchen', 'Prepare desired meals and snacks for the week\'s success.', 180, 0, 180, 'Sunday', NULL),
(32, 'Tidy Up', 'Daily', 'Bathroom', 'Reset status of the room.  Remove any trash or reorganize any items out of place.', 5, 0, 5, NULL, NULL),
(33, 'Clean Off Hair', 'Daily', 'Bathroom', 'Discard any hair left on any surfaces.', 5, 0, 5, NULL, NULL),
(34, 'Clean Toilet Seat', 'Daily', 'Bathroom', 'Wipe the exterior handle and seat with disinfectant.', 5, 0, 5, NULL, NULL),
(35, 'Put Clothes In Hamper', 'Daily', 'Bedroom', 'Place dirty clothes in laundry basket.', 10, 0, 10, NULL, NULL),
(36, 'Make Bed', 'Daily', 'Bedroom', 'Fluff pillows and restore them to the head of the bed.  Tuck in the sheets and comforter for desired presentation.', 5, 0, 5, NULL, NULL),
(37, 'Tidy Up', 'Daily', 'Livingroom', 'Reset status of room to have items return to their logical place.', 10, 0, 10, NULL, NULL),
(38, 'Put Away Dishes', 'Daily', 'Kitchen', 'Restore dishes to their logical starting position.', 5, 0, 5, NULL, NULL),
(39, 'Deep Mop', 'Monthly', 'Kitchen', 'Take extra time to really scrub down the high trafficked floor.', 15, 0, 15, NULL, '1'),
(40, 'Clean Fridge Interior', 'Monthly', 'Kitchen', 'Remove expired items. Wipe shelves with a mixture of baking soda and water to neutralize odors.', 30, 0, 30, NULL, '1'),
(41, 'Wipe Cabinets', 'Monthly', 'Kitchen', 'Focus on areas near handles. Use a damp cloth with wood-safe soap. Dry immediately to protect finish.', 10, 0, 10, NULL, '1'),
(42, 'Wash Comforter and Pillows', 'Monthly', 'Bedroom', 'Presoak pillows and run a laundry cycle for them and the bed comforter. ', 90, 0, 90, NULL, '2'),
(43, 'Straighten Shelves', 'Monthly', 'Livingroom', 'Reset shelves to for organization and ease.', 20, 0, 20, NULL, '2'),
(44, 'Dust Furniture, Decor, and Electronics', 'Monthly', 'Livingroom', 'Use a microfiber cloth. Start from the highest shelf and work your way down to prevent re-dusting.', 30, 0, 30, NULL, '2'),
(45, 'Wipe Down Appliances', 'Monthly', 'Livingroom', 'Wipe the exterior of the toaster, blender, and kettle. Remove crumbs from the toaster tray.', 15, 0, 15, NULL, '3'),
(46, 'Reorganize and Record Pantry Inventory', 'Monthly', 'Kitchen', 'Ensure the pantry has been supporting your dietary goals and has and makes items of most frequently used easily accessible.  Check expiration dates and toss anything past date.', 30, 0, 30, NULL, '3'),
(47, 'Declutter Mail/Papers', 'Monthly', 'Bedroom', 'Reorganize any papers and letters to a keep or toss pile. Discard the latter.', 5, 0, 5, NULL, '3'),
(48, 'Clean Desk', 'Monthly', 'Bedroom', 'Reorganize desk surface to have a fresh start and avoid clutter.', 10, 0, 10, NULL, '4'),
(49, 'Backup Phone', 'Monthly', 'Bedroom', 'Run backup application to store an encrypted copy of device on both physical media and maybe a cloud service.', 5, 0, 5, NULL, '4'),
(50, 'Declutter Email Inbox', 'Monthly', 'Bedroom', 'Work towards either getting unread emails down to zero or shrinking the size of the inbox in half.  May need to unsubscribe from unwanted newsletters.', 20, 0, 20, NULL, '4'),
(51, 'Backup Computer', 'Monthly', 'Bedroom', 'Run backup application to store an encrypted copy of device on both physical media and maybe a cloud service.', 30, 0, 30, NULL, '4'),
(52, 'Backup Ipad', 'Monthly', 'Bedroom', 'Run backup application to store an encrypted copy of device on both physical media and maybe a cloud service.', 15, 0, 15, NULL, '4'),
(53, 'Replace Sponge', 'Monthly', 'Kitchen', 'Discard old kitchen sponges. Sanitize the holder. Set out a fresh, clean sponge for use.', 2, 0, 2, NULL, '1'),
(54, 'Reset Monthly Calendar Whiteboard', 'Monthly', 'Bedroom', 'Erase previous months information and fill out the upcoming months plans.', 5, 0, 5, NULL, '1'),
(55, 'Clean Windows', 'Monthly', 'Livingroom', 'Use glass cleaner and a lint-free cloth. Clean in a S-pattern. Do not clean in direct sunlight to avoid streaks.', 10, 0, 10, NULL, '2'),
(56, 'Wipe Baseboards', 'Monthly', 'Kitchen', 'Use a damp cloth with mild soap. For tough scuffs, use a magic eraser. Dry with a clean towel.', 5, 0, 5, NULL, '1'),
(57, 'Wipe Baseboards', 'Monthly', 'Livingroom', 'Use a damp cloth with mild soap. For tough scuffs, use a magic eraser. Dry with a clean towel.', 5, 0, 5, NULL, '2'),
(58, 'Wipe Baseboards', 'Monthly', 'Bathroom', 'Use a damp cloth with mild soap. For tough scuffs, use a magic eraser. Dry with a clean towel.', 5, 0, 5, NULL, '3'),
(59, 'Scrub Bathtub', 'Monthly', 'Bathroom', 'Apply bathroom cleaner and let sit for 5 minutes. Scrub with a non-scratch pad. Rinse thoroughly with hot water.', 5, 0, 5, NULL, '3'),
(60, 'Clean Mirror', 'Weekly', 'Bathroom', 'Spray cleaner onto the cloth rather than the mirror to prevent \'black edge.\' Wipe in circles.', 5, 0, 5, 'Monday', NULL),
(61, 'Clean Oven', 'Monthly', 'Kitchen', 'Remove racks. Apply oven cleaner (ensure ventilation). Let sit as directed, then scrub and wipe clean.', 10, 0, 10, NULL, '4'),
(62, 'Clean Master Bathroom', 'Weekly', 'Bathroom', 'Dump bucket of water into toilet bowl to trigger a flush and have an empty bowl.  Use toilet bowl cleaner and scrub any water staining inside.  Sanitize all side of the toilet seat and handle.  Wipe down the sink bowl and handles.', 15, 0, 15, 'Monday', NULL),
(63, 'Sanitize Doorknobs', 'Monthly', 'Hallway', 'Use a disinfectant wipe or spray. Pay extra attention to high-traffic areas like the front door and kitchen.', 15, 0, 15, NULL, '4'),
(64, 'Wipe Light Switches', 'Monthly', 'Hallway', 'Lightly dampen a cloth with disinfectant (do not spray directly). Wipe the switch and the plate.', 5, 0, 5, NULL, '2'),
(65, 'Water Plants', 'Weekly', 'Outside', 'Check soil moisture first. Water at the base of the plant. Wipe dust off large leaves with a damp cloth.', 5, 0, 5, 'Saturday', NULL),
(66, 'Clean Coffee Maker', 'Weekly', 'Livingroom', 'Run a cycle with half vinegar and half water. Follow with two cycles of plain water. Wash the carafe.', 5, 0, 5, 'Saturday', NULL),
(67, 'Wash Pet Bowls', 'Monthly', 'Livingroom', 'Wash with hot soapy water or run through the dishwasher. Scrub the area surrounding the bowls.', 5, 0, 5, NULL, '1'),
(68, 'Sanitize Remote Controls', 'Monthly', 'Livingroom', 'Use a slightly damp alcohol wipe. Clean between buttons using a cotton swab if necessary.', 5, 0, 5, NULL, '1'),
(69, 'Refill Hand Soap', 'Monthly', 'Bathroom', 'Wipe the pump bottle before refilling. Ensure all bathrooms and the kitchen have adequate soap supply.', 5, 0, 5, NULL, '1'),
(70, 'Refill Hand Soap', 'Monthly', 'Kitchen', 'Wipe the pump bottle before refilling. Ensure all bathrooms and the kitchen have adequate soap supply.', 5, 0, 5, NULL, '1');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
