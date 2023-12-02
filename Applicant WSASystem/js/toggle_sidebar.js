 // TOGGLE SIDEBAR
 const menuBar = document.querySelector('#content nav .bx.bx-menu');
 const sidebar = document.getElementById('sidebar');

 function toggleSidebar() {
     sidebar.classList.toggle('hide');
 }

 menuBar.addEventListener('click', toggleSidebar);

 function handleResize() {
     const screenWidth = window.innerWidth;

     if (screenWidth <= 768) {
         sidebar.classList.add('hide');
     } else {
         sidebar.classList.remove('hide');
     }
 }

 window.addEventListener('resize', handleResize);

 handleResize();
