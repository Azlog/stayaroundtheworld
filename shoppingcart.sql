CREATE DATABASE IF NOT EXISTS `shoppingcart_advanced` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `shoppingcart_advanced`;

CREATE TABLE IF NOT EXISTS `accounts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `address_street` varchar(255) NOT NULL,
  `address_city` varchar(100) NOT NULL,
  `address_state` varchar(100) NOT NULL,
  `address_zip` varchar(50) NOT NULL,
  `address_country` varchar(100) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `accounts` (`id`, `email`, `password`, `first_name`, `last_name`, `address_street`, `address_city`, `address_state`, `address_zip`, `address_country`, `admin`) VALUES
(1, 'admin@codeshack.io', '$2y$10$pEHRAE4Ia0mE9BdLmbS.ueQsv/.WlTUSW7/cqF/T36iW.zDzSkx4y', 'John', 'Doe', '98 High Street', 'New York', 'NY', '10001', 'United States', 1);

CREATE TABLE IF NOT EXISTS `categories` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Sale'),
(2, 'Watches'),
(3, 'Accessories');

CREATE TABLE IF NOT EXISTS `discounts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `category_ids` varchar(50) NOT NULL,
  `product_ids` varchar(50) NOT NULL,
  `discount_code` varchar(50) NOT NULL,
  `discount_type` enum('Percentage','Fixed') NOT NULL,
  `discount_value` decimal(7,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `discounts` (`id`, `category_ids`, `product_ids`, `discount_code`, `discount_type`, `discount_value`, `start_date`, `end_date`) VALUES
(1, '', '', 'newyear2021', 'Percentage', '5.00', '2021-01-01 00:00:00', '2021-12-31 00:00:00');

CREATE TABLE IF NOT EXISTS `products` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `rrp` decimal(7,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL,
  `img` text NOT NULL,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `weight` decimal(7,2) NOT NULL DEFAULT '0.00',
  `download_url` varchar(255) NOT NULL DEFAULT '',
  `url_structure` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `products` (`id`, `name`, `description`, `price`, `rrp`, `quantity`, `img`, `date_added`, `weight`, `download_url`, `url_structure`) VALUES
(1, 'Smart Watch', '<p>Unique watch made with stainless steel, ideal for those that prefer interative watches.</p>\r\n<h3>Features</h3>\r\n<ul>\r\n<li>Powered by Android with built-in apps.</li>\r\n<li>Adjustable to fit most.</li>\r\n<li>Long battery life, continuous wear for up to 2 days.</li>\r\n<li>Lightweight design, comfort on your wrist.</li>\r\n</ul>', '29.99', '0.00', -1, 'watch.jpg', '2020-03-13 17:55:22', '0.00', '', 'smart-watch'),
(2, 'Wallet', '', '14.99', '19.99', -1, 'wallet.jpg', '2020-03-13 18:52:49', '0.00', '', ''),
(3, 'Headphones', '', '19.99', '0.00', -1, 'headphones.jpg', '2020-03-13 18:47:56', '34.00', '', ''),
(4, 'Digital Camera', '', '269.99', '0.00', 43772, 'camera.jpg', '2020-03-14 17:42:04', '0.00', '', '');

CREATE TABLE IF NOT EXISTS `products_categories` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `products_categories` (`id`, `product_id`, `category_id`) VALUES
(1, 1, 2),
(2, 2, 1);

CREATE TABLE IF NOT EXISTS `products_images` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

INSERT INTO `products_images` (`id`, `product_id`, `img`) VALUES
(1, 1, 'watch-2.jpg'),
(2, 1, 'watch-3.jpg'),
(3, 1, 'watch.jpg'),
(4, 2, 'wallet.jpg'),
(5, 3, 'headphones.jpg'),
(6, 4, 'camera.jpg');

CREATE TABLE IF NOT EXISTS `products_options` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `products_options` (`id`, `title`, `name`, `price`, `product_id`) VALUES
(1, 'Color', 'Black', '29.99', 1),
(2, 'Color', 'White', '32.99', 1),
(3, 'Color', 'Blue', '29.99', 1);

CREATE TABLE IF NOT EXISTS `shipping` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price_from` decimal(7,2) NOT NULL,
  `price_to` decimal(7,2) NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `weight_from` decimal(7,2) NOT NULL DEFAULT '0.00',
  `weight_to` decimal(7,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `shipping` (`id`, `name`, `price_from`, `price_to`, `price`, `weight_from`, `weight_to`) VALUES
(1, 'Standard', '0.00', '99999.00', '3.99', '0.00', '99999.00'),
(2, 'International', '0.00', '99999.00', '7.99', '0.00', '99999.00');

CREATE TABLE IF NOT EXISTS `transactions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(255) NOT NULL,
  `payment_amount` decimal(7,2) NOT NULL,
  `payment_status` varchar(30) NOT NULL,
  `created` datetime NOT NULL,
  `payer_email` varchar(255) NOT NULL DEFAULT '',
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `address_street` varchar(255) NOT NULL DEFAULT '',
  `address_city` varchar(100) NOT NULL DEFAULT '',
  `address_state` varchar(100) NOT NULL DEFAULT '',
  `address_zip` varchar(50) NOT NULL DEFAULT '',
  `address_country` varchar(100) NOT NULL DEFAULT '',
  `account_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'website',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `transactions_items` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(255) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_price` decimal(7,2) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_options` varchar(255) NOT NULL,
  `item_shipping_price` decimal(7,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


ALTER TABLE `accounts` ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `products_categories` ADD UNIQUE KEY `product_id` (`product_id`,`category_id`);

ALTER TABLE `products_images` ADD UNIQUE KEY `product_id` (`product_id`,`img`);

ALTER TABLE `transactions` ADD UNIQUE KEY `txn_id` (`txn_id`);
