/**
 * Product Badges
 * Validation script for Admin Products Tab
 */

document.addEventListener('DOMContentLoaded', function() {
    var listContainer = document.querySelector('.checkbox-list');
    if (!listContainer) return;

    var maxItems = parseInt(listContainer.getAttribute('data-max-items'), 10);
    if (isNaN(maxItems) || maxItems <= 0) return; // 0 significa sin límite

    var checkboxes = listContainer.querySelectorAll('input[name="productbadges[]"]');
    
    function updateCheckboxes() {
        var checkedCount = listContainer.querySelectorAll('input[name="productbadges[]"]:checked').length;
        
        checkboxes.forEach(function(cb) {
            if (!cb.checked) {
                cb.disabled = (checkedCount >= maxItems);
            }
        });
    }
    
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCheckboxes);
    });
    
    // Initial state check
    updateCheckboxes();
});
