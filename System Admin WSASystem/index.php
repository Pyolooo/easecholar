<?php

include 'connection.php';
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:user_login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:user_login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">

	<title>AdminModule</title>
</head>
<body>


	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<img src="img/isulogo.png">
			<span class="text">ISU Santiago Extension</span>
		</a>
		<ul class="side-menu top">
			<li class="active">
				<a href="#">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="scholarships.php">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Scholarship</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Analytics</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-message-dots' ></i>
					<span class="text">Message</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-group' ></i>
					<span class="text">Team</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
				</a>
			</li>
			<li>
				<a href="#" class="logout">
					<i class='bx bxs-log-out-circle' ></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->



	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<div class="menu">
				<i class='bx bx-menu'></i>
			</div>
			<div class="right-section">
				<div class="notif">
					<a href="#" class="notification">
						<i class='bx bxs-bell'></i>
						<span class="num">8</span>
					</a>
				</div>
				<div class="profile">
					<a href="admin_profile.php" class="profile">
						<img src="img/profile.png">
					</a>
				</div>
			</div>
		</nav>

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Dashboard</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Home</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
				<li>
					<i class='bx bxs-calendar-check' ></i>
					<?php include('connection.php'); ?>

						<?php
						$result = mysqli_query($dbConn,"SELECT * FROM tbl_scholarship");
						$num_rows = mysqli_num_rows($result);
						?>
					<span class="text">
						<h3><?php echo $num_rows; ?></h3>
						<p>Scholarship Applications</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3>2</h3>
						<p>Applicants</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-receipt'></i>
					<span class="text">
						<h3>2</h3>
						<p>Total Applications Received</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Recent Applicants</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>Applicant</th>
								<th>Date Submitted</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<img src="img/keanu.jpg">
									<p>John Keanu Cruz</p>
								</td>
								<td>01-10-2021</td>
								<td><span class="status completed">Completed</span></td>
							</tr>
							<tr>
								<td>
									<img src="img/daniela.jpg">
									<p>Daniela Soriano</p>
								</td>
								<td>01-10-2021</td>
								<td><span class="status pending">Pending</span></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="todo">
					<div class="head">
						<h3>Todos</h3>
						<i class='bx bx-plus' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<ul class="todo-list">
						<li class="completed">
							<p>Todo List</p>
							<i class='bx bx-dots-vertical-rounded' ></i>
						</li>
						<li class="completed">
							<p>Todo List</p>
							<i class='bx bx-dots-vertical-rounded' ></i>
						</li>
						<li class="not-completed">
							<p>Todo List</p>
							<i class='bx bx-dots-vertical-rounded' ></i>
						</li>
						<li class="completed">
							<p>Todo List</p>
							<i class='bx bx-dots-vertical-rounded' ></i>
						</li>
						<li class="not-completed">
							<p>Todo List</p>
							<i class='bx bx-dots-vertical-rounded' ></i>
						</li>
					</ul>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	

	<script src="script.js"></script>
</body>
</html>