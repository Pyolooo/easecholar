<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="scholarships.css">

	<title>SystemAdminModule</title>
</head>
<body>


	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<img src="img/isulogo.png">
			<span class="text">ISU Santiago Extension</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="index.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
            <li class="active">
				<a href="#">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Scholarships</span>
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


		
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Scholarships</h1>
					<ul class="breadcrumb">
						<li>
							<a href="scholarships.php">Scholarship</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Home</a>
						</li>
					</ul>
				</div>
				<a href="create_scholarship.php" class="btn-download">
					<i class='bx bx-plus'></i>
					<span class="text">Scholarship</span>
				</a>
			</div>



			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Available Scholarships</h3>
						<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
					</div>
					<table>
						<thead>
							<hr>
						</thead>
						<tbody>
							<?php
							include('connection.php');

							if ($dbConn->connect_error){
								die('Connection failed: ' . $dbConn->connect_errno);
							}

							$sql = "SELECT * FROM tbl_scholarship";
							$result = $dbConn->query($sql);

							if (!$result){
								die("Invalid query: " . $dbConn->connect_error);
							}

							while($row = $result->fetch_assoc()){
								echo "
								<tr>
									<td>
										$row[scholarship_id].
										<a href='scholarship_details.php?id=$row[scholarship_id]'>
											$row[scholarship]
										</a>
									</td>
								</tr>
								";
							}
							?>
							
						</tbody>
					</table>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	

	<script>
		const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
	const li = item.parentElement;

	item.addEventListener('click', function () {
		allSideMenu.forEach(i=> {
			i.parentElement.classList.remove('active');
		})
		li.classList.add('active');
	})
});




// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
})

const searchButton = document.getElementById("search-icon");
  const searchForm = document.querySelector("#content nav form");
  const searchInput = document.querySelector("#content nav form input[type='search']");

  searchButton.addEventListener("click", function () {
    if (searchForm.classList.contains("show")) {
      // Submit the form when the search button is clicked
      searchForm.submit();
    } else {
      // Show the search form when the search button is clicked
      searchForm.classList.add("show");
      searchInput.focus();
    }
  });


const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');


searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})





if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}


window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
})



const switchMode = document.getElementById('switch-mode');

switchMode.addEventListener('change', function () {
	if(this.checked) {
		document.body.classList.add('dark');
	} else {
		document.body.classList.remove('dark');
	}
})
	</script>
</body>
</html>