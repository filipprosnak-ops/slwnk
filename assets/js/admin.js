/**
 * MAHL Manager admin repeater behavior.
 */
(function () {
	'use strict';

	function createRowFromTemplate(template, index) {
		const html = template.innerHTML.replace(/__INDEX__/g, String(index));
		const table = document.createElement('table');

		table.innerHTML = '<tbody>' + html.trim() + '</tbody>';

		return table.querySelector('tr');
	}

	function getNextIndex(rowsContainer) {
		let maxIndex = -1;

		rowsContainer.querySelectorAll('tr[data-row-index]').forEach(function (row) {
			const rowIndex = parseInt(row.getAttribute('data-row-index'), 10);

			if (!Number.isNaN(rowIndex) && rowIndex > maxIndex) {
				maxIndex = rowIndex;
			}
		});

		return maxIndex + 1;
	}

	function ensureAtLeastOneRow(repeater, rowsContainer) {
		if (rowsContainer.querySelector('tr[data-row-index]')) {
			return;
		}

		const addButton = repeater.querySelector('[data-mahl-add-row]');

		if (addButton) {
			addButton.click();
		}
	}

	function initRepeater(repeater) {
		const rowsContainer = repeater.querySelector('tbody[data-mahl-rows]');
		const addButton = repeater.querySelector('[data-mahl-add-row]');

		if (!rowsContainer || !addButton) {
			return;
		}

		addButton.addEventListener('click', function (event) {
			const templateKey = addButton.getAttribute('data-mahl-add-row');
			const template = repeater.querySelector('template[data-mahl-template="' + templateKey + '"]');

			event.preventDefault();

			if (!template) {
				return;
			}

			const nextIndex = getNextIndex(rowsContainer);
			const row = createRowFromTemplate(template, nextIndex);

			if (row) {
				rowsContainer.appendChild(row);
			}
		});

		rowsContainer.addEventListener('click', function (event) {
			const removeButton = event.target.closest('[data-mahl-remove-row]');

			if (!removeButton) {
				return;
			}

			event.preventDefault();

			const row = removeButton.closest('tr[data-row-index]');

			if (row) {
				row.remove();
				ensureAtLeastOneRow(repeater, rowsContainer);
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-mahl-repeater]').forEach(function (repeater) {
			initRepeater(repeater);
		});
	});
}());
