<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="css/main.css">
	</head>
	<body>

		<div class="widget">

			<header>
				<h1>Latest Tweets</h1>
				<img src="images/twitter-icon.png">
			</header>

			<div class="search-wrapper">

				<input type="text" id="term" autocomplete="off" placeholder="Search for Tweets">
				<button type="button">Search Tweets</button>

			</div>

			<div class="count-wrapper">

				<div class="message">Tweets will refresh in  </div>
				<div id="countdown"></div>

			</div>

			<div class="tweets">

				<?php include('tweets.php'); ?>
					
			</div>
		</div>

	</body>
</html> 


<script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous"></script>
<script src="js/main.js"></script>
