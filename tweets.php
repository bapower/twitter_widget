<?php
	require_once('Controllers/TwitterWidgetController.php');

	$controller = new TwitterWidgetController();

	isset($_GET['search']) ? $search = $_GET['search'] : $search = '';

	$tweets = $controller->getEmbedHtml($search);

	if (empty($tweets)) {
		$tweets[] = '<p class="error">No tweets found for that search. Try again.</p>';
	}
	
?>

<?php foreach($tweets as $tweet) : ?>

	<div class="tweet">
		<?= $tweet ?>
	</div>

<?php endforeach ?>
