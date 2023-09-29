document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-button");
  const applicantsSection = document.getElementById("applicantsSection");
  const osaSection = document.getElementById("osaSection");
  const registrarSection = document.getElementById("registrarSection");

  // Initially, show Applicants section by default
  applicantsSection.style.display = "block";

  filterButtons.forEach(button => {
      button.addEventListener("click", function () {
          // Remove active class from all buttons
          filterButtons.forEach(btn => btn.classList.remove("active"));
          // Add active class to the clicked button
          button.classList.add("active");

          // Hide all sections
          applicantsSection.style.display = "none";
          osaSection.style.display = "none";
          registrarSection.style.display = "none";

          const selectedFilter = button.getAttribute("data-filter");

          // Show the selected section based on the filter
          if (selectedFilter === "applicants") {
              applicantsSection.style.display = "block";
          } else if (selectedFilter === "osa") {
              osaSection.style.display = "block";
          } else if (selectedFilter === "registrar") {
              registrarSection.style.display = "block";
          }
      });
  });
});