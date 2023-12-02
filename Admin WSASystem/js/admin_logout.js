function confirmLogout() {
  Swal.fire({
      title: "Logout",
      text: "Are you sure you want to log out?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, log out",
      cancelButtonText: "Cancel"
  }).then((result) => {
      if (result.isConfirmed) {
          window.location.href = "admin_logout.php";
      }
  });
}

document.querySelector(".logout").addEventListener("click", function(event) {
  event.preventDefault();
  confirmLogout();
});