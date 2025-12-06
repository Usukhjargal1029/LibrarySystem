document.addEventListener('DOMContentLoaded', function() {
  // --- Read More toggle ---
  const readMoreLink = document.getElementById('readMoreLink');
  const descText = document.getElementById('descText');
  const fullText = descText ? descText.dataset.fullText : '';
  const maxLength = 300;
  let expanded = false;

  function updateDesc() {
    if (!descText || !readMoreLink) return;

    if (expanded) {
      descText.textContent = fullText;
      readMoreLink.textContent = readMoreLink.dataset.showLess + ' ▴';
    } else {
      descText.textContent = fullText.length > maxLength ? fullText.substring(0, maxLength) + "..." : fullText;
      readMoreLink.textContent = readMoreLink.dataset.showMore + ' ▾';
    }
  }

  if (descText && readMoreLink) {
    updateDesc();
    readMoreLink.onclick = function(e) {
      e.preventDefault();
      expanded = !expanded;
      updateDesc();
    };
  }

  // --- Star rating ---
  const stars = document.querySelectorAll('.star-rating .fa-star');
  const starRating = document.querySelector('.star-rating');

  if (stars.length > 0 && starRating) {
    const bookId = starRating.dataset.bookId;
    const userRating = parseInt(starRating.dataset.userRating) || 0;

    // Pre-fill stars if user already rated
    stars.forEach(s => {
      if (parseInt(s.dataset.value) <= userRating) {
        s.classList.add('selected');
        s.style.color = '#f5b301';
      }
    });

    stars.forEach(star => {
      star.addEventListener('mouseenter', () => {
        const val = parseInt(star.dataset.value);
        stars.forEach(s => s.style.color = s.dataset.value <= val ? '#f5b301' : '#ccc');
      });

      star.addEventListener('mouseleave', () => {
        stars.forEach(s => s.style.color = s.classList.contains('selected') ? '#f5b301' : '#ccc');
      });

      star.addEventListener('click', () => {
        const val = parseInt(star.dataset.value);
        stars.forEach(s => {
          s.classList.toggle('selected', s.dataset.value <= val);
          s.style.color = s.dataset.value <= val ? '#f5b301' : '#ccc';
        });

        // Send rating to server
        fetch('rate_books.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ book_id: bookId, rating: val })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) alert(`You rated this book ${val} stars!`);
          else alert('Error: ' + data.message);
        })
        .catch(err => console.error('Rating error:', err));
      });
    });
  }
});
