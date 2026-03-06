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
				updateRowState(row);
			}
		});

		rowsContainer.addEventListener('change', function (event) {
			const row = event.target.closest('tr[data-row-index]');

			if (!row) {
				return;
			}

			if (event.target.matches('[data-mahl-team-select], [data-mahl-event-type-select]')) {
				updateRowState(row);
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

		rowsContainer.querySelectorAll('tr[data-row-index]').forEach(function (row) {
			updateRowState(row);
		});
	}

	function filterPlayerSelects(row) {
		const teamSelect = row.querySelector('[data-mahl-team-select]');
		const selectedTeamId = teamSelect ? teamSelect.value : '';

		row.querySelectorAll('[data-mahl-player-select]').forEach(function (select) {
			const currentValue = select.value;

			select.querySelectorAll('option').forEach(function (option) {
				const optionTeamId = option.getAttribute('data-mahl-team-id');

				if (!optionTeamId) {
					option.hidden = false;
					option.disabled = false;
					return;
				}

				const isVisible = !selectedTeamId || optionTeamId === selectedTeamId;

				option.hidden = !isVisible;
				option.disabled = !isVisible;
			});

			if (currentValue && select.selectedOptions.length && select.selectedOptions[0].disabled) {
				select.value = '';
			}
		});
	}

	function toggleEventFields(row) {
		const eventTypeSelect = row.querySelector('[data-mahl-event-type-select]');
		const eventType = eventTypeSelect ? eventTypeSelect.value : 'goal';
		const assistFields = row.querySelectorAll('[data-mahl-player-select="assist-1"], [data-mahl-player-select="assist-2"]');
		const penaltyField = row.querySelector('[data-mahl-penalty-minutes]');

		if (!eventTypeSelect) {
			return;
		}

		assistFields.forEach(function (select) {
			const shouldDisable = eventType === 'penalty';

			select.disabled = shouldDisable;

			if (shouldDisable) {
				select.value = '';
			}
		});

		if (penaltyField) {
			const shouldDisablePenalty = eventType !== 'penalty';

			penaltyField.disabled = shouldDisablePenalty;

			if (shouldDisablePenalty) {
				penaltyField.value = '0';
			}
		}
	}

	function updateRowState(row) {
		filterPlayerSelects(row);
		toggleEventFields(row);
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-mahl-repeater]').forEach(function (repeater) {
			initRepeater(repeater);
		});
	});
}());
