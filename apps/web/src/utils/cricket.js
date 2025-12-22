/**
 * Cricket Utility Functions
 */

/**
 * Gets the correctly formatted score string for a specific team, handling innings logic.
 * 
 * @param {Object} score - The score object containing innings1 and innings2
 * @param {String|Number} teamId - The ID of the team to get the score for
 * @returns {String} Formatted score string (e.g., "150/4 (20.0)") or empty string
 */
export const getInningScore = (score, teamId) => {
    if (!score) return ''
    let inning = null

    // Check both innings to see if batting_team_id matches the team we want
    if (score.innings1 && parseInt(score.innings1.batting_team_id) === parseInt(teamId)) {
        inning = score.innings1
    } else if (score.innings2 && parseInt(score.innings2.batting_team_id) === parseInt(teamId)) {
        inning = score.innings2
    }

    if (!inning) return ''

    return formatScore(inning)
}

/**
 * Formats a raw innings object into a standardized string
 * 
 * @param {Object} inning - The innings object with runs, wickets, overs
 * @returns {String} Formatted string
 */
export const formatScore = (inning) => {
    if (!inning) return ''

    let text = ''
    if (inning.runs !== undefined || inning.wickets !== undefined) {
        text = `${inning.runs}/${inning.wickets}`
    }

    if (inning.overs) {
        text += ` (${inning.overs})`
    }

    return text
}
