/**
 * Utility functions for campaign-related operations
 */

/**
 * Format amount with proper locale and currency formatting
 * @param {number|string} amount 
 * @returns {string}
 */
export const formatAmount = (amount) => {
    return parseFloat(amount || 0).toLocaleString('en-US', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    })
}

/**
 * Format date to a readable format
 * @param {string} date 
 * @param {string} fallback - Custom fallback message when date is null/empty
 * @returns {string}
 */
export const formatDate = (date, fallback = 'No date set') => {
    if (!date) return fallback
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    })
}

/**
 * Format date with time
 * @param {string} dateTime 
 * @returns {string}
 */
export const formatDateTime = (dateTime) => {
    if (!dateTime) return 'Never'
    return new Date(dateTime).toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

/**
 * Calculate progress percentage
 * @param {Object} campaign 
 * @returns {number}
 */
export const getProgressPercentage = (campaign) => {
    try{
        if (!campaign.goal_amount || campaign.goal_amount === '0.00' || campaign.current_amount === '0.00') return 0
        return Math.round((parseFloat(campaign.current_amount) / parseFloat(campaign.goal_amount)) * 100)
    } catch (error) {
        return 0
    }
}

/**
 * Get CSS classes for campaign status
 * @param {string} status 
 * @returns {string}
 */
export const getStatusClass = (status) => {
    const key = (status || '').toString().toLowerCase()
    const classes = {
        'active': 'bg-green-600 text-white border border-green-600',
        'inactive': 'bg-gray-600 text-white border border-gray-600',
        'completed': 'bg-blue-600 text-white border border-blue-600',
        'cancelled': 'bg-red-600 text-white border border-red-600'
    }

    return classes[key] || 'bg-gray-600 text-white border border-gray-600'
}
