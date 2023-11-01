const search = document.querySelector('.input-group input');
const table_rows = document.querySelectorAll('tbody tr');
const table_headings = document.querySelectorAll('thead th');
let searchTerm = ''; // Initialize the search term

// 1. Searching for specific data of HTML table
search.addEventListener('input', () => {
    searchTerm = search.value.toLowerCase(); // Update the search term
    searchTable();
    showPage(currentPage); // Reapply search when switching pages
    updatePaginationButtons();
});

function searchTable() {
    table_rows.forEach((row, i) => {
        let table_data = row.textContent.toLowerCase();

        // Check if the row matches the search term
        if (searchTerm === '' || table_data.includes(searchTerm)) {
            row.classList.remove('hide');
        } else {
            row.classList.add('hide');
        }
        row.style.setProperty('--delay', i / 25 + 's');
    });

    document.querySelectorAll('tbody tr:not(.hide)').forEach((visible_row, i) => {
        visible_row.style.backgroundColor = (i % 2 == 0) ? 'transparent' : '#0000000b';
    });
}


table_headings.forEach((head, i) => {
    let sort_asc = true;
    head.onclick = () => {
        table_headings.forEach(head => head.classList.remove('active'));
        head.classList.add('active');

        document.querySelectorAll('td').forEach(td => td.classList.remove('active'));
        table_rows.forEach(row => {
            row.querySelectorAll('td')[i].classList.add('active');
        })

        head.classList.toggle('asc', sort_asc);
        sort_asc = head.classList.contains('asc') ? false : true;

        sortTable(i, sort_asc);
    }
})


function sortTable(column, sort_asc) {
    [...table_rows].sort((a, b) => {
        let first_row = a.querySelectorAll('td')[column].textContent.toLowerCase(),
            second_row = b.querySelectorAll('td')[column].textContent.toLowerCase();

        return sort_asc ? (first_row < second_row ? 1 : -1) : (first_row < second_row ? -1 : 1);
    })
        .map(sorted_row => document.querySelector('tbody').appendChild(sorted_row));
}

const table = document.querySelector('table');
const rows = table.querySelectorAll('tbody tr');
const rowsPerPage = 20;
let currentPage = 1;

function showPage(page) {
  const start = (page - 1) * rowsPerPage;
  const end = page * rowsPerPage;

  rows.forEach((row, index) => {
    if (index >= start && index < end) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

function updatePaginationButtons() {
  const totalPages = Math.ceil(rows.length / rowsPerPage);

  const paginationContainer = document.querySelector('.pagination');
  paginationContainer.innerHTML = '';

  if (totalPages > 1) {
    for (let i = 1; i <= totalPages; i++) {
      const pageButton = document.createElement('button');
      pageButton.textContent = i;
      pageButton.addEventListener('click', () => {
        currentPage = i;
        showPage(currentPage);
        updatePaginationButtons();
      });
      if (i === currentPage) {
        pageButton.classList.add('active');
      }
      pageButton.classList.add('pagination-button'); // Add a class
      paginationContainer.appendChild(pageButton);
    }

// Add "Previous" button
if (currentPage > 1) {
    const prevButton = document.createElement('button');
    prevButton.textContent = 'Previous';
    prevButton.classList.add('pagination-button'); // Add a class
    prevButton.addEventListener('click', () => {
      currentPage--;
      showPage(currentPage);
      updatePaginationButtons();
    });
    paginationContainer.insertBefore(prevButton, paginationContainer.firstChild);
  }
  
  // Add "Next" button
  if (currentPage < totalPages) {
    const nextButton = document.createElement('button');
    nextButton.textContent = 'Next';
    nextButton.classList.add('pagination-button'); // Add a class
    nextButton.addEventListener('click', () => {
      currentPage++;
      showPage(currentPage);
      updatePaginationButtons();
    });
    paginationContainer.appendChild(nextButton);
  }
  
  }
}

showPage(currentPage);
updatePaginationButtons();
