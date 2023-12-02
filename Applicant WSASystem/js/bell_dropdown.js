
    function toggleDropdown() {
      $(".dropdown").toggleClass("active");
      $(".num").hide();
    }

    $(document).ready(function() {
      function toggleBellActive() {
        $("#bellIcon").toggleClass("active");
      }

          // Click event handler for the bell icon
      $("#bellIcon").on("click", function() {
        toggleBellActive();
        toggleDropdown();
        });
    });
