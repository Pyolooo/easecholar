function toggleDropdown() {
  $(".num").hide();
}

$(".notification .bxs-bell").on("click", function(event) {
  event.stopPropagation();
  $(".dropdown").toggleClass("active");
  toggleDropdown();
  if ($(".dropdown").hasClass("active")) {
      markAllNotificationsAsRead();
  } else {}
});

$(document).on("click", function() {
  $(".dropdown").removeClass("active");
});
