/**
 * ScancoorDesign Admin JavaScript
 * Handles the variants configuration interface
 */

(function($) {
	'use strict';
	
	$(document).ready(function() {
		console.log('ScancoorDesign Admin JS loaded');
		
		// Tab switching
		$('.nav-tab').on('click', function(e) {
			e.preventDefault();
			
			var target = $(this).attr('href');
			
			// Update tabs
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			
			// Update content
			$('.ScancoorDesign-tab-content').removeClass('active');
			$(target).addClass('active');
		});
		
		// Add new option
		$(document).on('click', '.add-option', function() {
			var type = $(this).data('type');
			var $tbody = $('#' + type + '-list');
			var template = $('#' + type + '-template').html();
			
			// Remove "no items" row if exists
			$tbody.find('.no-items').remove();
			
			// Get next index
			var index = $tbody.find('tr').length;
			var num = index + 1;
			
			// Replace placeholders
			var newRow = template.replace(/__INDEX__/g, index).replace(/__NUM__/g, num);
			
			// Append row
			$tbody.append(newRow);
			
			// Focus first input
			$tbody.find('tr:last input:first').focus();
		});
		
		// Delete option
		$(document).on('click', '.delete-option', function() {
			if (!confirm('¿Estás seguro de eliminar esta opción?')) {
				return;
			}
			
			var $row = $(this).closest('tr');
			var $tbody = $row.closest('tbody');
			
			$row.fadeOut(300, function() {
				$(this).remove();
				
				// If no rows left, show "no items" message
				if ($tbody.find('tr').length === 0) {
					var colspan = $tbody.closest('table').find('thead th').length;
					$tbody.append(
						'<tr class="no-items">' +
						'<td colspan="' + colspan + '" style="text-align:center;">' +
						'No hay opciones configuradas. Haz clic en "Agregar Nueva" abajo.' +
						'</td>' +
						'</tr>'
					);
				} else {
					// Re-number rows
					$tbody.find('tr').each(function(index) {
						$(this).find('td:first').text(index + 1);
					});
				}
			});
		});
		
		// Form submit confirmation
		$('#ScancoorDesign-config-form').on('submit', function() {
			var hasChanges = true; // Could implement change tracking
			
			if (hasChanges) {
				return confirm('¿Guardar todos los cambios?');
			}
			
			return true;
		});
		
		// Auto-save warning on page leave
		var formChanged = false;
		
		$('#ScancoorDesign-config-form').on('change', 'input', function() {
			formChanged = true;
		});
		
		$(window).on('beforeunload', function() {
			if (formChanged) {
				return '¿Estás seguro? Hay cambios sin guardar.';
			}
		});
		
		$('#ScancoorDesign-config-form').on('submit', function() {
			formChanged = false;
		});
	});
	
})(jQuery);
