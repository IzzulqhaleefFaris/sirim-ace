<?php
// Placeholder dashboard page while under development.
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Under Development</title>
	<style>
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
			background: #f7f7f7;
			color: #222;
		}
		.page {
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			text-align: center;
			padding: 24px;
		}
		.card {
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
			padding: 32px 28px;
			max-width: 420px;
			width: 100%;
		}
		.card h1 {
			margin: 0 0 8px;
			font-size: 24px;
			letter-spacing: 0.2px;
		}
		.card p {
			margin: 0 0 20px;
			color: #555;
			line-height: 1.5;
		}
		.back-btn {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #0f62fe;
			color: #fff;
			text-decoration: none;
			border: none;
			border-radius: 8px;
			padding: 10px 16px;
			font-size: 14px;
			cursor: pointer;
		}
		.back-btn:hover {
			background: #0353e9;
		}
	</style>
</head>
<body>
	<div class="page">
		<div class="card">
			<h1>Page Under Development</h1>
			<p>This page is being built. Please check back soon.</p>
			<button class="back-btn" type="button" onclick="window.history.back()">
				Back to Previous Page
			</button>
		</div>
	</div>
</body>
</html>
