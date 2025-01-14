-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 06, 2025 at 08:21 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `EchoLog`
--

-- --------------------------------------------------------

--
-- Table structure for table `Games`
--

CREATE TABLE `Games` (
  `game_id` int(11) NOT NULL,
  `game_title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `cover_image` varchar(355) DEFAULT NULL,
  `banner_image` varchar(355) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Admin_recommend` tinyint(1) NOT NULL DEFAULT 0,
  `Like_count` int(11) DEFAULT 0,
  `Wishlist_count` int(11) DEFAULT 0,
  `Publisher` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Games`
--

INSERT INTO `Games` (`game_id`, `game_title`, `description`, `genre`, `release_date`, `platform`, `cover_image`, `banner_image`, `created_at`, `Admin_recommend`, `Like_count`, `Wishlist_count`, `Publisher`) VALUES
(1, 'The Last of Us', 'An ambiguous world. Set in a post-apocalyptic 2023, The Last of Us presents a world that\'s been ravaged by a pandemic caused by a fungus called “cordyceps” (terrifyingly, a real fungus), which turns its hosts into violent zombie-like creatures whose only goal is to spread the infection.', 'Action, Adventure', '2013-06-14', 'PS3, PS4, PS5, PC', 'https://images.hepsiburada.net/description-assets/description-prod-30/49cdec35-b8c4-4876-a5a9-1549e6ef243a.jpg', 'https://m.media-amazon.com/images/I/51bmCaPjoWL.jpg', '2024-12-23 17:01:28', 1, 100, 100, 'Naughty Dog'),
(2, 'Days Gone', 'Ride and fight into a deadly, post pandemic America. Play as Deacon St. John, a drifter and bounty hunter who rides the broken road, fighting to survive while searching for a reason to live in this open-world action-adventure game', 'Action, Adventure, Open World', '2019-04-26', 'PS4, PS5, PC', 'https://upload.wikimedia.org/wikipedia/ru/b/bf/Days_Gone_game_cover.jpeg', 'https://media.wired.com/photos/5cc0bb5c45cd172bacbe2b78/master/pass/Culture_DaysGone_Review.jpg', '2024-12-23 17:01:28', 0, 1, 1, 'Bend Studio'),
(3, 'Red Dead Redemption 2', 'America, 1899.Arthur Morgan and the Van der Linde gang are outlaws on the run. With federal agents and the best bounty hunters in the nation massing on their heels, the gang must rob, steal and fight their way across the rugged heartland of America in order to survive. As deepening internal divisions threaten to tear the gang apart, Arthur must make a choice between his own ideals and loyalty to the gang who raised him', 'Action, Adventure, Open World', '2018-10-26', 'PS4, Xbox One, PC', 'https://assets.vg247.com/current//2018/05/red_dead_redemption_2_cover_art_1.jpg', 'https://media.gq-magazine.co.uk/photos/5d13ae1a84b769790ffd4aa9/16:9/w_2560%2Cc_limit/rdr2-07-gq-25oct18_b.jpg', '2024-12-23 17:01:28', 1, 201, 150, 'Rockstar Games'),
(4, 'Cyberpunk 2077', 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the megalopolis of Night City, where you play as a cyberpunk mercenary wrapped up in a do-or-die fight for survival. Improved and featuring all-new free additional content, customize your character and playstyle as you take on jobs, build a reputation, and unlock upgrades. The relationships you forge and the choices you make will shape the story and the world around you. Legends are made here. What will yours be?', 'RPG, Action', '2020-12-10', 'PS4, PS5, Xbox One, Xbox Series X|S, PC', 'https://static.wikia.nocookie.net/g-c-a/images/0/0b/Cover-art-6.jpg/revision/latest?cb=20210104020439', 'https://i.pcmag.com/imagery/articles/00hYjpCCgBH5VFYPsZrBqmf-1..v1600444074.jpg', '2024-12-23 17:01:28', 1, 110, 90, 'CD Projekt Red'),
(5, 'Elden Ring', 'Uncover a mysterious world filled with challenging combat.', 'Action, RPG', '2022-02-25', 'PS4, PS5, Xbox One, Xbox Series X|S, PC', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQtM3UV4Y7JElw8-s25cywmqdcWu6_ruLwvOg&s', 'https://preview.redd.it/1hj1qvuzg0p91.jpg?width=640&crop=smart&auto=webp&s=cdca6dd007fcf62f117380a9db8ec9b0a7db7203', '2024-12-23 17:01:28', 1, 1, 1, 'FromSoftware'),
(6, 'Grand Theft Auto V', 'Experience the life of crime in Los Santos in this open-world masterpiece.', 'Action, Open World', '2013-09-17', 'PS4, PS5, Xbox One, Xbox Series X|S, PC', 'https://hips.hearstapps.com/digitalspyuk.cdnds.net/13/14/gaming-gta5-cover.jpeg', 'https://img.tamindir.com/2022/04/470608/gta-5-giris-ekraninda-kaliyor.jpg', '2024-12-23 17:01:28', 1, 0, 0, 'Rockstar Games'),
(7, 'Hollow Knight', 'Explore a dark, interconnected world in this indie masterpiece.', 'Action, Metroidvania', '2017-02-24', 'PC, Switch, PS4, Xbox One', 'https://static.wikia.nocookie.net/hollowknight/images/a/a1/HK_first_cover_art.png/revision/latest?cb=20201215163524', 'https://images.squarespace-cdn.com/content/v1/59066a076b8f5b6083962bff/fc6e8494-52e8-4f3d-b528-5e2d9ed52370/VH_01_1080pjpg.jpg', '2024-12-23 17:01:28', 0, 1, 0, 'Team Cherry'),
(8, 'God of War (2018)', 'Embark on a journey of fatherhood and vengeance in Norse mythology.', 'Action, Adventure', '2018-04-20', 'PS4, PS5, PC', 'https://m.media-amazon.com/images/M/MV5BNjJiNTFhY2QtNzZkYi00MDNiLWEzNGEtNWE1NzBkOWIxNmY5XkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg', 'https://phenixxgaming.com/wp-content/uploads/2019/10/god-of-war-2018.jpg', '2024-12-23 17:01:28', 1, 0, 1, 'Santa Monica Studio'),
(9, 'The Witcher 3: Wild Hunt', 'Dive into a sprawling RPG filled with choices and consequences.', 'RPG, Action, Open World', '2015-05-19', 'PS4, PS5, Xbox One, Xbox Series X|S, PC', 'https://image.api.playstation.com/vulcan/ap/rnd/202211/0711/qezXTVn1ExqBjVjR5Ipm97IK.png', 'https://www.meme-arsenal.com/memes/8212a4749c47d11ecb943d0b7d287b43.jpg', '2024-12-23 17:01:28', 1, 0, 0, 'CD Projekt Red'),
(10, 'Minecraft', 'Create and explore infinite worlds in this sandbox phenomenon.', 'Sandbox, Survival', '2011-11-18', 'PC, PS4, Xbox One, Switch, Mobile', 'https://m.media-amazon.com/images/M/MV5BNjQzMDlkNDctYmE3Yi00ZWFiLTlmOWYtMjI4MzQ4Y2JhZjY2XkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg', 'https://www.minecraft.net/content/dam/minecraftnet/games/minecraft/key-art/Vanilla-PMP_Collection-Carousel-0_Buzzy-Bees_1280x768.jpg', '2024-12-23 17:01:28', 0, 0, 0, 'Mojang Studios');

-- --------------------------------------------------------

--
-- Table structure for table `Likes_Games`
--

CREATE TABLE `Likes_Games` (
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Likes_Games`
--

INSERT INTO `Likes_Games` (`user_id`, `game_id`) VALUES
(12, 3),
(12, 5),
(12, 7),
(15, 2);

--
-- Triggers `Likes_Games`
--
DELIMITER $$
CREATE TRIGGER `DecreaseGameLikes` AFTER DELETE ON `Likes_Games` FOR EACH ROW BEGIN 
	UPDATE Games
    SET Like_count = Like_count - 1
    WHERE game_id = OLD.game_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `IncrementGameLikes` AFTER INSERT ON `Likes_Games` FOR EACH ROW BEGIN
	UPDATE Games
    SET Like_count = Like_count + 1
    WHERE game_id = NEW.game_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Likes_Playlists`
--

CREATE TABLE `Likes_Playlists` (
  `user_id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Likes_Playlists`
--

INSERT INTO `Likes_Playlists` (`user_id`, `playlist_id`) VALUES
(1, 14);

--
-- Triggers `Likes_Playlists`
--
DELIMITER $$
CREATE TRIGGER `DecrementPlaylistLikes` AFTER DELETE ON `Likes_Playlists` FOR EACH ROW BEGIN
    UPDATE Playlist
    SET like_count = like_count - 1
    WHERE playlist_id = OLD.playlist_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `IncrementPlaylistLikes` AFTER INSERT ON `Likes_Playlists` FOR EACH ROW BEGIN
    UPDATE Playlist
    SET like_count = like_count + 1
    WHERE playlist_id = NEW.playlist_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Playlist`
--

CREATE TABLE `Playlist` (
  `playlist_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `playlist_name` varchar(100) NOT NULL,
  `playlist_description` varchar(350) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `like_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Playlist`
--

INSERT INTO `Playlist` (`playlist_id`, `user_id`, `playlist_name`, `playlist_description`, `created_at`, `like_count`) VALUES
(1, 1, 'HELL YEAH Games', 'Games that makes you say \"HELL YEAH\" and characters are cool as fuck fr', '2024-12-23 17:17:03', 0),
(2, 1, 'MC is literally Me', 'Hell yeah MC is literally me, based on ME!', '2024-12-29 19:13:27', 0),
(3, 1, 'Worth a shot Games', 'Worth to play a once', '2024-12-29 19:13:46', 0),
(4, 1, 'Games that deserves sequals', 'Games that needs sequal asap', '2024-12-29 19:30:32', 0),
(6, 1, 'Dark and Deep Stories', 'No happy ending, just a sad and dark stories', '2024-12-29 19:56:34', 0),
(7, 1, 'I\'am Vengeance', 'Best games about Batman', '2024-12-29 19:57:40', 0),
(13, 1, 'Must have Games', 'games that you must play at least once', '2024-12-29 20:16:09', 0),
(14, 12, 'Games that make you feel like God', 'Games that make you feel like god', '2024-12-29 20:22:39', 100),
(17, 1, 'The worst games ', 'asdasd', '2025-01-01 18:50:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Playlist_Games`
--

CREATE TABLE `Playlist_Games` (
  `playlist_games_id` int(11) NOT NULL,
  `playlist_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Playlist_Games`
--

INSERT INTO `Playlist_Games` (`playlist_games_id`, `playlist_id`, `game_id`) VALUES
(18, 13, 2),
(19, 13, 3),
(20, 13, 9),
(47, 14, 1),
(48, 14, 2),
(49, 14, 5),
(50, 14, 9),
(31, 17, 1),
(32, 17, 7);

-- --------------------------------------------------------

--
-- Table structure for table `Ratings`
--

CREATE TABLE `Ratings` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Ratings`
--

INSERT INTO `Ratings` (`rating_id`, `user_id`, `game_id`, `rating`, `created_at`) VALUES
(1, 1, 4, 5, '2024-12-23 17:12:48'),
(2, 12, 5, 4, '2025-01-01 11:32:19'),
(3, 1, 10, 5, '2025-01-01 18:45:47'),
(4, 12, 1, 5, '2025-01-04 14:08:34'),
(5, 1, 2, 4, '2025-01-06 18:57:25'),
(6, 15, 7, 5, '2025-01-06 18:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `REVIEWS`
--

CREATE TABLE `REVIEWS` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `review_text` text NOT NULL,
  `like_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `REVIEWS`
--

INSERT INTO `REVIEWS` (`review_id`, `user_id`, `game_id`, `review_text`, `like_count`, `created_at`) VALUES
(1, 1, 4, 'Did 4 years of waiting, paid off? Fucking yes, writing, characters, gameplay, humor everything is perfect. Writing is so good that you want to rewatch cutscenes many times.', 1002, '2024-12-23 17:12:26'),
(3, 12, 9, 'this game is so awesome', 0, '2024-12-30 22:59:20'),
(5, 12, 5, 'Game is based that, i havent took shower for months!', 0, '2025-01-01 11:32:19'),
(6, 1, 10, 'This game is awesome', 0, '2025-01-01 18:45:47'),
(7, 12, 1, 'The game is a cinematic experience that could make a rock emotional, offering newcomers and seasoned zombifiers alike the chance to experience that jaw-dropping, heart-pounding feeling', 101, '2025-01-04 14:08:34'),
(8, 13, 1, 'Unforgettable characters, sky-high stakes, nail-biting action, and impeccable pacing make The Last of Us Part I a superlative gaming experience in every possible sense.', 0, '2025-01-04 14:22:16'),
(9, 1, 2, 'Game is sooo long especially in final chapter, unnceasry missions, but overally i loved it!', 0, '2025-01-06 18:57:25'),
(10, 15, 7, 'Indie Gem', 0, '2025-01-06 18:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `review_likes`
--

CREATE TABLE `review_likes` (
  `user_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_likes`
--

INSERT INTO `review_likes` (`user_id`, `review_id`) VALUES
(12, 1),
(15, 1),
(15, 7);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `user_nickname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `user_nickname`, `email`, `password`, `profile_image`, `created_at`) VALUES
(1, 'fxidirzade', 'adminzade@gmail.com', '$2y$10$om1dvuNGX.JfwNUSH.rhyuOfCQyh5go1tAC2ZWcY43WvJhUi18pXG', 'https://i.pinimg.com/736x/31/e5/92/31e5920d5027d7d39d396651c2bb5c30.jpg', '2024-12-23 17:09:46'),
(2, 'deacon_john', 'deacon@gmail.com', 'FuckSkizzo69', 'https://www.jammable.com/cdn-cgi/image/width=3840,quality=25,format=webp/https://imagecdn.voicify.ai/models/74ccdd2e-ba43-4e6b-b3d5-61789f019c16.png', '2024-12-23 17:10:44'),
(5, 'John Marston', 'john@outlaw.com', 'john69', 'default.png', '2024-12-25 11:30:38'),
(6, 'ted_mosby', 'mosby@design.com', '$2y$10$6erpJ5JCr.Fs7MKE0OmYnOEEag1hoGlYyLLmEz76AYRF.wnHNKe2K', 'default.png', '2024-12-25 22:29:15'),
(9, 'Silverhand', 'johnny@rocker.com', '$2y$10$bYTIesBUqNIxDMNAPQHBve.NptiWluMyUlTPZLpT9Sg/Wnqw4voAW', 'default.png', '2024-12-25 22:43:09'),
(12, 'Terry_KingDavis', 'terry@temple.com', '$2y$10$kaKEYNB4s4fRoENnvXOS9.WvjijtgYud6yBZr.md6RJgA4ieEP6C.', 'default.png', '2024-12-25 23:00:53'),
(13, 'linusnotechtips', 'linus@mail.com', '$2y$10$CJLbpVSBVkM21ieQfCRjt./m9ROJpAeNkJfnz8foyRekIarpz4rRK', 'default.png', '2024-12-25 23:03:29'),
(14, 'Victor', 'v@cyberpunk.com', '$2y$10$mvd8GXuI0TNPRvuleJofX.Q6BGrRQLSG3C1b8fWJ6uc.15/vyP1P.', 'default.png', '2024-12-25 23:04:07'),
(15, 'Solid_Snake', 'snake@gmail.com', '$2y$10$TRtDZpzWg3rK.6KMhMaxB.z6jV80I3Dv7V3qhaZJ4i23c3XjhdRga', 'https://static.wikia.nocookie.net/metalgear/images/f/fb/MGS1SnakePP.png/revision/latest/scale-to-width/360?cb=20230819182226', '2025-01-06 18:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `WISHLIST`
--

CREATE TABLE `WISHLIST` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `WISHLIST`
--

INSERT INTO `WISHLIST` (`wishlist_id`, `user_id`, `game_id`, `created_at`) VALUES
(3, 12, 8, '2024-12-26 22:23:58'),
(81, 12, 1, '2025-01-04 21:28:09'),
(82, 12, 5, '2025-01-04 22:05:07'),
(83, 15, 2, '2025-01-06 18:51:45');

--
-- Triggers `WISHLIST`
--
DELIMITER $$
CREATE TRIGGER `DecrementWishLikes` AFTER DELETE ON `WISHLIST` FOR EACH ROW BEGIN
    UPDATE Games
    SET Wishlist_count = Wishlist_count - 1
    WHERE game_id = OLD.game_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `IncreaseWishlistGames` AFTER INSERT ON `WISHLIST` FOR EACH ROW BEGIN 
	UPDATE GAMES 
    SET Wishlist_count = Wishlist_count + 1
    WHERE game_id = NEW.game_id;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Games`
--
ALTER TABLE `Games`
  ADD PRIMARY KEY (`game_id`);

--
-- Indexes for table `Likes_Games`
--
ALTER TABLE `Likes_Games`
  ADD PRIMARY KEY (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `Likes_Playlists`
--
ALTER TABLE `Likes_Playlists`
  ADD PRIMARY KEY (`user_id`,`playlist_id`),
  ADD KEY `playlist_id` (`playlist_id`);

--
-- Indexes for table `Playlist`
--
ALTER TABLE `Playlist`
  ADD PRIMARY KEY (`playlist_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Playlist_Games`
--
ALTER TABLE `Playlist_Games`
  ADD PRIMARY KEY (`playlist_games_id`),
  ADD UNIQUE KEY `playlist_id` (`playlist_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `Ratings`
--
ALTER TABLE `Ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `REVIEWS`
--
ALTER TABLE `REVIEWS`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`user_id`,`review_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_nickname` (`user_nickname`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `WISHLIST`
--
ALTER TABLE `WISHLIST`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Games`
--
ALTER TABLE `Games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Playlist`
--
ALTER TABLE `Playlist`
  MODIFY `playlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `Playlist_Games`
--
ALTER TABLE `Playlist_Games`
  MODIFY `playlist_games_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `Ratings`
--
ALTER TABLE `Ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `REVIEWS`
--
ALTER TABLE `REVIEWS`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `WISHLIST`
--
ALTER TABLE `WISHLIST`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Likes_Games`
--
ALTER TABLE `Likes_Games`
  ADD CONSTRAINT `likes_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `likes_games_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`game_id`);

--
-- Constraints for table `Likes_Playlists`
--
ALTER TABLE `Likes_Playlists`
  ADD CONSTRAINT `likes_playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `likes_playlists_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `Playlist` (`playlist_id`);

--
-- Constraints for table `Playlist`
--
ALTER TABLE `Playlist`
  ADD CONSTRAINT `playlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Playlist_Games`
--
ALTER TABLE `Playlist_Games`
  ADD CONSTRAINT `playlist_games_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `Playlist` (`playlist_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_games_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `Ratings`
--
ALTER TABLE `Ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `REVIEWS`
--
ALTER TABLE `REVIEWS`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`game_id`) ON DELETE CASCADE;

--
-- Constraints for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`review_id`) REFERENCES `REVIEWS` (`review_id`);

--
-- Constraints for table `WISHLIST`
--
ALTER TABLE `WISHLIST`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`game_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
