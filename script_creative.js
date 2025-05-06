document.addEventListener('DOMContentLoaded', function() {
    const background = document.querySelector('.creative-background');
    const elements = document.querySelectorAll('.creative-background > div');
    
    // Randomize initial positions
    elements.forEach(el => {
      // Random position
      const posX = Math.random() * 80 + 10;
      const posY = Math.random() * 80 + 10;
      
      // Random size
      const size = Math.random() * 0.5 + 0.5;
      
      // Apply styles
      el.style.left = `${posX}%`;
      el.style.top = `${posY}%`;
      el.style.transform = `scale(${size})`;
      
      // Random animation duration
      const duration = Math.random() * 10 + 5;
      el.style.animationDuration = `${duration}s`;
    });
    
    // Make elements move slowly around the screen
    function floatElements() {
      elements.forEach(el => {
        // Get current position
        let currentX = parseFloat(el.style.left);
        let currentY = parseFloat(el.style.top);
        
        // Calculate new position (slow drift)
        const newX = currentX + (Math.random() * 2 - 1);
        const newY = currentY + (Math.random() * 2 - 1);
        
        // Keep within bounds
        el.style.left = `${Math.max(5, Math.min(95, newX))}%`;
        el.style.top = `${Math.max(5, Math.min(95, newY))}%`;
        
        // Random scale change
        const currentScale = parseFloat(el.style.transform.replace('scale(', '').replace(')', ''));
        const newScale = currentScale + (Math.random() * 0.02 - 0.01);
        el.style.transform = `scale(${Math.max(0.3, Math.min(1, newScale))})`;
      });
      
      requestAnimationFrame(floatElements);
    }
    
    // Start floating animation
    floatElements();
  });