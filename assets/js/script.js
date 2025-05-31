document.addEventListener('DOMContentLoaded', () => {
  const slides   = document.querySelectorAll('#testimonials .testimonial');
  const prevBtn  = document.querySelector('#testimonials .prev');
  const nextBtn  = document.querySelector('#testimonials .next');
  let current    = 0;
  let animating  = false;

  function goToSlide(newIndex, direction) {
    if (animating || newIndex === current) return;
    animating = true;
    const outgoing = slides[current];
    const incoming = slides[newIndex];

    // prepare incoming off-screen
    incoming.style.display   = 'block';
    incoming.style.transform = `translateX(${ direction==='next'? '100%':'-100%' })`;
    incoming.style.opacity   = 0;

    // force reflow
    incoming.getBoundingClientRect();

    // animate both
    outgoing.style.transition = incoming.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
    outgoing.style.transform  = `translateX(${ direction==='next'? '-100%':'100%' })`;
    outgoing.style.opacity    = 0;
    incoming.style.transform  = 'translateX(0)';
    incoming.style.opacity    = 1;

    incoming.addEventListener('transitionend', function handler() {
      // cleanup
      outgoing.style.display   = 'none';
      outgoing.classList.remove('active');
      outgoing.style.transition = outgoing.style.transform = outgoing.style.opacity = '';
      incoming.classList.add('active');
      incoming.style.transition = incoming.style.transform = incoming.style.opacity = '';
      incoming.removeEventListener('transitionend', handler);
      current = newIndex;
      animating = false;
    });
  }

  prevBtn.addEventListener('click', () => {
    const idx = (current - 1 + slides.length) % slides.length;
    goToSlide(idx, 'prev');
  });

  nextBtn.addEventListener('click', () => {
    const idx = (current + 1) % slides.length;
    goToSlide(idx, 'next');
  });

  // auto‑advance every 5s
  setInterval(() => {
    const idx = (current + 1) % slides.length;
    goToSlide(idx, 'next');
  }, 5000);
});
document.getElementById('user-icon').addEventListener('click', function(e) {
  e.preventDefault();
  const popup = document.getElementById('user-popup');
  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
});
// Optional: Close it if user clicks outside
window.addEventListener('click', function(e) {
  const popup = document.getElementById('user-popup');
  const icon = document.getElementById('user-icon');
  if (!popup.contains(e.target) && !icon.contains(e.target)) {
    popup.style.display = 'none';
  }
});
