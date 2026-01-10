// Load user email from session storage
window.addEventListener('DOMContentLoaded', () => {
    const userEmail = sessionStorage.getItem('userEmail');
    const userId = sessionStorage.getItem('userId');
    
    if (!userEmail || !userId) {
        // Redirect back to main page if no email is stored
        window.location.href = 'index.html';
        return;
    }
    
    // Display user email
    document.getElementById('userEmail').textContent = userEmail;
    
    // Setup character counters
    setupCharacterCounters();
});

// Setup character counters for text areas
function setupCharacterCounters() {
    const favoriteGames = document.getElementById('favoriteGames');
    const additionalInfo = document.getElementById('additionalInfo');
    
    favoriteGames.addEventListener('input', (e) => {
        const count = e.target.value.length;
        document.getElementById('gamesCharCount').textContent = count;
        if (count > 500) {
            e.target.value = e.target.value.substring(0, 500);
            document.getElementById('gamesCharCount').textContent = 500;
        }
    });
    
    additionalInfo.addEventListener('input', (e) => {
        const count = e.target.value.length;
        document.getElementById('infoCharCount').textContent = count;
        if (count > 1000) {
            e.target.value = e.target.value.substring(0, 1000);
            document.getElementById('infoCharCount').textContent = 1000;
        }
    });
}

// Validate survey form
function validateSurvey() {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    
    // Check gambler type (at least one must be selected)
    const gamblerTypes = document.querySelectorAll('input[name="gamblerType"]:checked');
    if (gamblerTypes.length === 0) {
        document.getElementById('gamblerTypeError').textContent = 'Please select at least one option';
        isValid = false;
    }
    
    // Check bonus type (at least one must be selected)
    const bonusTypes = document.querySelectorAll('input[name="bonusType"]:checked');
    if (bonusTypes.length === 0) {
        document.getElementById('bonusTypeError').textContent = 'Please select at least one option';
        isValid = false;
    }
    
    return isValid;
}

// Handle survey submission
async function handleSurveySubmit(event) {
    event.preventDefault();
    
    if (!validateSurvey()) {
        return false;
    }
    
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'block';
    document.querySelector('.submit-btn').disabled = true;
    
    try {
        const userId = sessionStorage.getItem('userId');
        
        // Collect survey data
        const gamblerTypes = Array.from(document.querySelectorAll('input[name="gamblerType"]:checked'))
            .map(el => el.value);
        const bonusTypes = Array.from(document.querySelectorAll('input[name="bonusType"]:checked'))
            .map(el => el.value);
        
        const surveyData = {
            userId: userId,
            gamblerTypes: gamblerTypes,
            bonusTypes: bonusTypes,
            favoriteGames: document.getElementById('favoriteGames').value.trim(),
            additionalInfo: document.getElementById('additionalInfo').value.trim(),
            language: document.getElementById('languageSelect').value
        };
        
        // Send to backend
        const response = await fetch('api/submit-survey.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(surveyData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear session storage
            sessionStorage.removeItem('userEmail');
            sessionStorage.removeItem('userId');
            
            // Show completion message and redirect
            showCompletionMessage();
        } else {
            alert('Error: ' + (result.message || 'Failed to submit survey. Please try again.'));
            document.getElementById('loadingIndicator').style.display = 'none';
            document.querySelector('.submit-btn').disabled = false;
        }
    } catch (error) {
        console.error('Survey submission error:', error);
        alert('An error occurred. Please try again later.');
        document.getElementById('loadingIndicator').style.display = 'none';
        document.querySelector('.submit-btn').disabled = false;
    }
    
    return false;
}

// Skip survey
function skipSurvey() {
    if (confirm('Are you sure you want to skip? We use this info to send you better offers.')) {
        // Clear session storage
        sessionStorage.removeItem('userEmail');
        sessionStorage.removeItem('userId');
        
        // Show completion message and redirect
        showCompletionMessage();
    }
}

// Show completion message
function showCompletionMessage() {
    // Get shortId from session storage (preferred)
    let userId = sessionStorage.getItem('shortId');

    // Fallback to regular userId if shortId doesn't exist
    // This handles cases where the database migration hasn't been run yet
    if (!userId) {
        userId = sessionStorage.getItem('userId');
        console.warn('shortId not found in sessionStorage, falling back to userId. Database migration may not have been run.');
    }

    // Redirect to recommended offers page
    if (userId) {
        // Small delay to show completion, then redirect
        setTimeout(() => {
            window.location.href = `recommended-for-you.html?user=${userId}`;
        }, 1500);
    } else {
        console.error('Neither shortId nor userId found in sessionStorage');
    }

    // Show success message briefly before redirect
    document.querySelector('.survey-container').innerHTML = `
        <div style="text-align: center; padding: 40px 20px;">
            <div class="success-icon" style="margin: 0 auto 24px;">✓</div>
            <h2 class="survey-title" data-translate="complete-title">You're All Set!</h2>
            <p class="survey-subtitle" data-translate="complete-subtitle" style="margin-bottom: 30px;">
                Redirecting you to your personalized offers...
            </p>
        </div>
    `;

    // Update progress indicator
    document.querySelectorAll('.progress-step').forEach(step => {
        step.classList.add('completed');
        step.classList.remove('active');
    });

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Language change (placeholder - will use translations.js)
function changeLanguage(lang) {
    // This will be implemented in translations.js
    console.log('Language changed to:', lang);
}
