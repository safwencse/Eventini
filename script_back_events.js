document.addEventListener('DOMContentLoaded', function() {
    // Create additional floating elements
    const animatedBg = document.querySelector('.animated-background');
    
    // Add some random floating elements
    for (let i = 0; i < 15; i++) {
      const shape = document.createElement('div');
      shape.classList.add('floating-shape');
      
      // Random shape type
      const shapeType = Math.random() > 0.5 ? 'circle' : 'triangle';
      shape.classList.add(shapeType);
      
      // Random position and size
      const size = Math.random() * 20 + 10;
      const posX = Math.random() * 100;
      const delay = Math.random() * 15;
      const duration = Math.random() * 20 + 15;
      
      // Apply styles
      shape.style.width = `${size}px`;
      shape.style.height = `${size}px`;
      shape.style.left = `${posX}%`;
      shape.style.animationDelay = `${delay}s`;
      shape.style.animationDuration = `${duration}s`;
      
      // Random opacity
      const opacity = Math.random() * 0.3 + 0.1;
      shape.style.opacity = opacity;
      
      // Random color variation
      const hue = Math.random() * 30 + 15; // Orange hue range
      shape.style.backgroundColor = `hsla(${hue}, 100%, 50%, ${opacity})`;
      
      animatedBg.appendChild(shape);
    }
  });