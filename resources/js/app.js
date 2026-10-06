const pageDraftFields = {
	'pages::dashboard.counters': ['counters'],
	'pages::dashboard.play-table': [
		'cards',
		'name',
		'type',
		'colors',
		'power',
		'toughness',
		'description',
		'dieSides',
		'dieCount',
		'rolls',
		'showPresets',
	],
	'dashboard.sessions-footer': ['sessionName', 'playedOn', 'expanded'],
};

const draftLifetime = 7 * 24 * 60 * 60 * 1000;

function initializeDashboardDrafts() {
	if (!window.Livewire?.hook || window.Livewire.dashboardDraftsInitialized) return;

	const userId = document.body?.dataset.draftUser;
	if (!userId) return;

	window.Livewire.dashboardDraftsInitialized = true;

	const storageKey = `token-builder:workspace-drafts:v1:${userId}`;
	const components = new Map();

	function readDrafts() {
		try {
			const drafts = JSON.parse(localStorage.getItem(storageKey) || '{}');
			return drafts && typeof drafts === 'object' && !Array.isArray(drafts) ? drafts : {};
		} catch {
			return {};
		}
	}

	function writeDrafts(drafts) {
		try {
			localStorage.setItem(storageKey, JSON.stringify(drafts));
		} catch {
			// Storage may be unavailable or full; keep the active page usable.
		}
	}

	function validDraftValue(field, value) {
		if (field === 'counters') {
			return Array.isArray(value) && value.length <= 64 && value.every(Number.isInteger);
		}

		if (field === 'colors') {
			return Array.isArray(value) && value.length <= 10 && value.every((color) => typeof color === 'string');
		}

		if (field === 'rolls') {
			return Array.isArray(value) && value.length <= 10 && value.every(Number.isInteger);
		}

		if (field === 'cards') {
			return Array.isArray(value) && value.length <= 100 && value.every((card) => card && typeof card === 'object');
		}

		if (['power', 'toughness', 'dieSides', 'dieCount'].includes(field)) {
			return Number.isInteger(value);
		}

		if (['showPresets', 'expanded'].includes(field)) return typeof value === 'boolean';
		if (['name', 'type', 'description', 'sessionName', 'playedOn'].includes(field)) return typeof value === 'string';

		return false;
	}

	function persistComponent(component) {
		const fields = pageDraftFields[component.name];
		if (!fields) return;

		const drafts = readDrafts();
		const values = {};

		for (const field of fields) {
			const value = component.ephemeral[field];
			if (validDraftValue(field, value)) values[field] = value;
		}

		drafts[component.name] = { ...values, savedAt: Date.now() };
		writeDrafts(drafts);
	}

	function restoreComponent(component) {
		const fields = pageDraftFields[component.name];
		if (!fields) return;

		const drafts = readDrafts();
		const saved = drafts[component.name];
		if (!saved) return;

		if (!Number.isFinite(saved.savedAt) || Date.now() - saved.savedAt > draftLifetime) {
			delete drafts[component.name];
			writeDrafts(drafts);
			return;
		}

		let restored = false;
		for (const field of fields) {
			if (validDraftValue(field, saved[field])) {
				component.ephemeral[field] = saved[field];
				restored = true;
			}
		}

		if (!restored) return;

		component.el.setAttribute('data-draft-restoring', '');
		window.setTimeout(() => {
			Promise.resolve(component.$wire.$refresh()).finally(() => {
				component.el.removeAttribute('data-draft-restoring');
			});
		}, 0);
	}

	function registerComponent(component, cleanup = () => {}) {
		if (!pageDraftFields[component.name]) return;

		components.set(component.id, component);
		restoreComponent(component);
		cleanup(() => components.delete(component.id));
	}

	window.Livewire.hook('component.init', ({ component, cleanup }) => registerComponent(component, cleanup));
	window.Livewire.hook('commit', ({ component, succeed }) => {
		if (!pageDraftFields[component.name]) return;

		persistComponent(component);
		succeed(() => persistComponent(component));
	});

	function persistActivePages() {
		for (const component of components.values()) persistComponent(component);
	}

	document.addEventListener('livewire:navigate', persistActivePages);
	window.addEventListener('beforeunload', persistActivePages);

	window.addEventListener('dashboard-session-loaded', (event) => {
		const drafts = readDrafts();
		const savedAt = Date.now();

		drafts['pages::dashboard.play-table'] = {
			...(drafts['pages::dashboard.play-table'] || {}),
			cards: event.detail.cards || [],
			savedAt,
		};
		drafts['pages::dashboard.counters'] = {
			...(drafts['pages::dashboard.counters'] || {}),
			counters: event.detail.counters || [],
			savedAt,
		};

		writeDrafts(drafts);
	});

	document.addEventListener('token-builder-save-session', () => {
		const root = document.querySelector('[data-session-footer][wire\\:id]');
		if (!root) return;

		const footer = window.Livewire.find(root.getAttribute('wire:id'));
		if (footer) footer.saveSession(readDrafts());
	});

	document.querySelectorAll('[wire\\:id]').forEach((element) => {
		const component = window.Livewire.find(element.getAttribute('wire:id'))?.__instance;
		if (component && !components.has(component.id)) registerComponent(component);
	});
}

if (window.Livewire?.hook) {
	initializeDashboardDrafts();
} else {
	document.addEventListener('livewire:init', initializeDashboardDrafts, { once: true });
}
