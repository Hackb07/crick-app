/**
 * Ball Ticker Component
 * Animated microcomponent for each ball entry
 */

class BallTicker {
    static create(runs, type = 'runs') {
        const ticker = document.createElement('span');
        ticker.className = 'ball-ticker';
        
        // Add type-specific class
        if (runs === 4) {
            ticker.classList.add('runs-4');
        } else if (runs === 6) {
            ticker.classList.add('runs-6');
        } else if (type === 'wicket') {
            ticker.classList.add('wicket');
            ticker.textContent = 'W';
        } else {
            ticker.textContent = runs;
        }
        
        // Animate entry
        ticker.style.animation = 'ballTick 0.3s ease-out';
        
        return ticker;
    }
    
    static addToContainer(container, runs, type = 'runs') {
        const ticker = BallTicker.create(runs, type);
        container.appendChild(ticker);
        
        // Remove after animation if needed (for temporary display)
        // Otherwise keep in DOM
        return ticker;
    }
    
    static createBallSequence(container, balls) {
        // Create a sequence of balls
        container.innerHTML = ''; // Clear existing
        
        balls.forEach((ball, index) => {
            setTimeout(() => {
                const ticker = BallTicker.create(ball.runs || 0, ball.type || 'runs');
                container.appendChild(ticker);
            }, index * 100); // Stagger animation
        });
    }
}

// Export for use in scoring interface
if (typeof window !== 'undefined') {
    window.BallTicker = BallTicker;
}

