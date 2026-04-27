/**
 * 顧客管理システム - 改善版
 * モダンなJavaScript機能を活用し、保守性とセキュリティを強化
 */

// 設定管理クラス
class Config {
    static #instance = null;

    constructor() {
        if (Config.#instance) {
            return Config.#instance;
        }

        this.API = Object.freeze({
            BASE_URL: this.#getApiUrl(),
            ENDPOINTS: Object.freeze({
                CUSTOMERS: '/customers',
                CUSTOMER_DETAIL: '/customers/:id'
            }),
            TIMEOUT: 10000,
            RETRY_ATTEMPTS: 3,
            RETRY_DELAY_BASE: 1000
        });

        this.UI = Object.freeze({
            DEBOUNCE_DELAY: 300,
            ERROR_DISPLAY_DURATION: 5000,
            LOADING_DELAY: 200
        });

        this.VALIDATION = Object.freeze({
            VALID_STATUSES: ['active', 'inactive', 'pending', 'suspended'],
            MAX_NAME_LENGTH: 100,
            MAX_EMAIL_LENGTH: 255
        });

        Config.#instance = this;
        Object.freeze(this);
    }

    #getApiUrl() {
        try {
            const url = window.ENV?.API_URL || '/api';
            if (!this.#isValidUrl(url)) {
                throw new Error('Invalid API URL format');
            }
            return url;
        } catch (error) {
            console.error('Failed to get API URL:', error);
            return '/api'; // フォールバック
        }
    }

    #isValidUrl(url) {
        return /^https?:\/\//.test(url) || url.startsWith('/');
    }

    static getInstance() {
        return new Config();
    }
}

// カスタムエラークラス
class CustomerManagerError extends Error {
    constructor(message, code, originalError = null) {
        super(message);
        this.name = 'CustomerManagerError';
        this.code = code;
        this.originalError = originalError;
        this.timestamp = new Date().toISOString();
    }
}

class ValidationError extends CustomerManagerError {
    constructor(message, field, value, originalError = null) {
        super(message, 'VALIDATION_ERROR', originalError);
        this.name = 'ValidationError';
        this.field = field;
        this.value = value;
    }
}

class NetworkError extends CustomerManagerError {
    constructor(message, status, originalError = null) {
        super(message, 'NETWORK_ERROR', originalError);
        this.name = 'NetworkError';
        this.status = status;
    }
}

// ユーティリティクラス
class SecurityUtils {
    // XSS対策のHTMLエスケープ（改良版）
    static escapeHtml(str) {
        if (str == null) return '';

        const escapeMap = new Map([
            ['&', '&amp;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
            ['"', '&quot;'],
            ["'", '&#39;'],
            ['/', '&#x2F;'],
            ['`', '&#x60;'],
            ['=', '&#x3D;']
        ]);

        return String(str).replace(/[&<>"'/`=]/g, char => escapeMap.get(char));
    }

    // CSRFトークンの取得（改良版）
    static getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            console.warn('CSRF token not found in meta tag');
        }
        return token || null;
    }

    // 入力値のサニタイズ
    static sanitizeInput(input, maxLength = 1000) {
        if (typeof input !== 'string') {
            input = String(input);
        }
        return input.trim().slice(0, maxLength);
    }
}

class DOMUtils {
    // 安全な要素作成
    static createElement(tag, attributes = {}, textContent = '') {
        const element = document.createElement(tag);

        Object.entries(attributes).forEach(([key, value]) => {
            if (key === 'className') {
                element.className = value;
            } else if (key.startsWith('data-') || key.startsWith('aria-')) {
                element.setAttribute(key, value);
            } else {
                element[key] = value;
            }
        });

        if (textContent) {
            element.textContent = textContent;
        }

        return element;
    }

    // 要素の安全な削除
    static safeRemove(element) {
        if (element && element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }

    // デバウンス機能付きイベントリスナー
    static addDebouncedListener(element, event, handler, delay = 300) {
        let timeoutId;
        const debouncedHandler = (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => handler(...args), delay);
        };

        element.addEventListener(event, debouncedHandler);

        return () => {
            clearTimeout(timeoutId);
            element.removeEventListener(event, debouncedHandler);
        };
    }
}

// HTTP クライアントクラス
class HttpClient {
    constructor(config) {
        this.config = config;
        this.activeRequests = new Set();
    }

    async request(url, options = {}) {
        const controller = new AbortController();
        const requestId = Symbol('request');

        this.activeRequests.add({ controller, id: requestId });

        try {
            const response = await this.#fetchWithRetry(url, {
                ...options,
                signal: controller.signal,
                headers: this.#getHeaders(options.headers)
            });

            return await this.#handleResponse(response);
        } catch (error) {
            throw this.#handleError(error);
        } finally {
            this.activeRequests.delete(
                [...this.activeRequests].find(req => req.id === requestId)
            );
        }
    }

    async #fetchWithRetry(url, options, retryCount = 0) {
        const timeoutId = setTimeout(
            () => options.signal?.abort(),
            this.config.API.TIMEOUT
        );

        try {
            const response = await fetch(url, options);
            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new NetworkError(
                    `HTTP ${response.status}: ${response.statusText}`,
                    response.status
                );
            }

            return response;
        } catch (error) {
            clearTimeout(timeoutId);

            if (error.name === 'AbortError' || retryCount >= this.config.API.RETRY_ATTEMPTS) {
                throw error;
            }

            if (this.#isRetryableError(error)) {
                const delay = this.config.API.RETRY_DELAY_BASE * Math.pow(2, retryCount);
                await this.#sleep(delay);
                return this.#fetchWithRetry(url, options, retryCount + 1);
            }

            throw error;
        }
    }

    #getHeaders(additionalHeaders = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...additionalHeaders
        };

        const csrfToken = SecurityUtils.getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return headers;
    }

    async #handleResponse(response) {
        const contentType = response.headers.get('content-type');

        if (contentType?.includes('application/json')) {
            return await response.json();
        }

        return await response.text();
    }

    #handleError(error) {
        if (error instanceof NetworkError) {
            return error;
        }

        if (error.name === 'AbortError') {
            return new CustomerManagerError('Request was cancelled', 'REQUEST_CANCELLED', error);
        }

        return new CustomerManagerError(
            'Network request failed',
            'NETWORK_FAILURE',
            error
        );
    }

    #isRetryableError(error) {
        return error.name === 'TypeError' ||
               (error.message && error.message.includes('fetch')) ||
               (error instanceof NetworkError && error.status >= 500);
    }

    #sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    cancelAllRequests() {
        this.activeRequests.forEach(({ controller }) => {
            controller.abort();
        });
        this.activeRequests.clear();
    }
}

// データバリデーター
class CustomerValidator {
    constructor(config) {
        this.config = config;
    }

    validateCustomer(customer, isPartial = false) {
        const errors = [];

        if (!isPartial || customer.hasOwnProperty('id')) {
            if (!this.#isValidId(customer.id)) {
                errors.push(new ValidationError('Invalid customer ID', 'id', customer.id));
            }
        }

        if (!isPartial || customer.hasOwnProperty('name')) {
            if (!this.#isValidName(customer.name)) {
                errors.push(new ValidationError('Invalid customer name', 'name', customer.name));
            }
        }

        if (customer.hasOwnProperty('email') && customer.email) {
            if (!this.#isValidEmail(customer.email)) {
                errors.push(new ValidationError('Invalid email format', 'email', customer.email));
            }
        }

        if (customer.hasOwnProperty('status')) {
            if (!this.#isValidStatus(customer.status)) {
                errors.push(new ValidationError('Invalid status', 'status', customer.status));
            }
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    #isValidId(id) {
        return id != null && (typeof id === 'number' || typeof id === 'string') && String(id).trim() !== '';
    }

    #isValidName(name) {
        return typeof name === 'string' &&
               name.trim().length > 0 &&
               name.length <= this.config.VALIDATION.MAX_NAME_LENGTH;
    }

    #isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return typeof email === 'string' &&
               email.length <= this.config.VALIDATION.MAX_EMAIL_LENGTH &&
               emailRegex.test(email);
    }

    #isValidStatus(status) {
        return this.config.VALIDATION.VALID_STATUSES.includes(status);
    }

    sanitizeCustomer(customer) {
        return {
            id: customer.id,
            name: SecurityUtils.escapeHtml(SecurityUtils.sanitizeInput(customer.name || '')),
            email: SecurityUtils.escapeHtml(SecurityUtils.sanitizeInput(customer.email || '')),
            status: this.#isValidStatus(customer.status) ? customer.status : 'unknown',
            createdAt: customer.createdAt || customer.created_at || new Date().toISOString()
        };
    }
}

// UI管理クラス
class UIManager {
    constructor(config) {
        this.config = config;
        this.loadingTimeout = null;
    }

    showLoading(show = true, delay = 0) {
        const loadingElement = document.getElementById('loadingIndicator');
        if (!loadingElement) return;

        if (this.loadingTimeout) {
            clearTimeout(this.loadingTimeout);
        }

        if (show && delay > 0) {
            this.loadingTimeout = setTimeout(() => {
                this.#setLoadingVisibility(loadingElement, true);
            }, delay);
        } else {
            this.#setLoadingVisibility(loadingElement, show);
        }
    }

    #setLoadingVisibility(element, show) {
        element.style.display = show ? 'block' : 'none';
        element.setAttribute('aria-hidden', String(!show));
    }

    showError(message, duration = this.config.UI.ERROR_DISPLAY_DURATION) {
        const container = this.#getOrCreateErrorContainer();
        container.textContent = message;
        container.style.display = 'block';
        container.setAttribute('role', 'alert');
        container.className = 'alert alert-danger';

        if (duration > 0) {
            setTimeout(() => {
                container.style.display = 'none';
            }, duration);
        }
    }

    showSuccess(message, duration = this.config.UI.ERROR_DISPLAY_DURATION) {
        const container = this.#getOrCreateErrorContainer();
        container.textContent = message;
        container.style.display = 'block';
        container.setAttribute('role', 'status');
        container.className = 'alert alert-success';

        if (duration > 0) {
            setTimeout(() => {
                container.style.display = 'none';
            }, duration);
        }
    }

    #getOrCreateErrorContainer() {
        let container = document.getElementById('messageContainer');

        if (!container) {
            container = DOMUtils.createElement('div', {
                id: 'messageContainer',
                className: 'alert',
                'aria-live': 'polite'
            });
            container.style.display = 'none';
            const mainContainer = document.querySelector('.container') || document.body;
            mainContainer.insertBefore(container, mainContainer.firstChild);
        }

        return container;
    }

    renderCustomers(customers, container) {
        if (!container) {
            console.error('Customer list container not found');
            return;
        }

        if (customers.length === 0) {
            this.#renderEmptyState(container);
            return;
        }

        const fragment = document.createDocumentFragment();

        // テーブル形式で表示
        const table = this.#createCustomerTable(customers);
        fragment.appendChild(table);

        // 一度にDOMを更新してリフローを最小化
        container.innerHTML = '';
        container.appendChild(fragment);
    }

    #renderEmptyState(container) {
        const emptyElement = DOMUtils.createElement('div', {
            className: 'alert alert-info',
            'role': 'status'
        }, '該当する顧客が見つかりませんでした。');

        container.innerHTML = '';
        container.appendChild(emptyElement);
    }

    #createCustomerTable(customers) {
        const table = DOMUtils.createElement('table', {
            className: 'table table-hover'
        });

        // ヘッダー
        const thead = DOMUtils.createElement('thead');
        const headerRow = DOMUtils.createElement('tr');
        ['ID', '名前', 'メール', '電話番号', 'ステータス', '操作'].forEach(text => {
            const th = DOMUtils.createElement('th', {}, text);
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        // ボディ
        const tbody = DOMUtils.createElement('tbody');
        customers.forEach(customer => {
            const row = this.#createCustomerRow(customer);
            tbody.appendChild(row);
        });
        table.appendChild(tbody);

        return table;
    }

    #createCustomerRow(customer) {
        const row = DOMUtils.createElement('tr', {
            'data-customer-id': customer.id
        });

        // ID
        row.appendChild(DOMUtils.createElement('td', {}, String(customer.id)));

        // 名前
        row.appendChild(DOMUtils.createElement('td', {}, customer.name));

        // メール
        row.appendChild(DOMUtils.createElement('td', {}, customer.email || '-'));

        // 電話番号
        row.appendChild(DOMUtils.createElement('td', {}, customer.phone || '-'));

        // ステータス
        const statusCell = DOMUtils.createElement('td');
        const statusBadge = DOMUtils.createElement('span', {
            className: `badge bg-${this.#getStatusColor(customer.status)}`
        }, this.#getStatusLabel(customer.status));
        statusCell.appendChild(statusBadge);
        row.appendChild(statusCell);

        // 操作ボタン
        const actionCell = DOMUtils.createElement('td');
        const viewBtn = DOMUtils.createElement('a', {
            href: `/customers/${customer.id}`,
            className: 'btn btn-sm btn-info me-1'
        }, '詳細');
        const editBtn = DOMUtils.createElement('a', {
            href: `/customers/${customer.id}/edit`,
            className: 'btn btn-sm btn-warning me-1'
        }, '編集');
        actionCell.appendChild(viewBtn);
        actionCell.appendChild(editBtn);
        row.appendChild(actionCell);

        return row;
    }

    #getStatusColor(status) {
        const colors = {
            'active': 'success',
            'inactive': 'secondary',
            'pending': 'warning',
            'suspended': 'danger'
        };
        return colors[status] || 'secondary';
    }

    #getStatusLabel(status) {
        const labels = {
            'active': '有効',
            'inactive': '無効',
            'pending': '保留中',
            'suspended': '停止'
        };
        return labels[status] || status || '不明';
    }
}

// メインの顧客管理クラス
class CustomerManager {
    constructor(options = {}) {
        this.config = Config.getInstance();
        this.httpClient = new HttpClient(this.config);
        this.validator = new CustomerValidator(this.config);
        this.uiManager = new UIManager(this.config);

        this.customers = [];
        this.customerCache = new Map();
        this.eventListeners = [];
        this.options = options;

        this.#init();
    }

    async #init() {
        try {
            this.#bindEvents();
            if (this.options.autoLoad !== false) {
                await this.fetchCustomers();
            }
        } catch (error) {
            console.error('Failed to initialize CustomerManager:', error);
            this.uiManager.showError('システムの初期化に失敗しました。');
        }
    }

    #bindEvents() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');

        if (searchInput) {
            const removeListener = DOMUtils.addDebouncedListener(
                searchInput,
                'input',
                () => this.searchCustomers(),
                this.config.UI.DEBOUNCE_DELAY
            );
            this.eventListeners.push(removeListener);
        }

        if (statusFilter) {
            const handler = () => this.searchCustomers();
            statusFilter.addEventListener('change', handler);
            this.eventListeners.push(() => {
                statusFilter.removeEventListener('change', handler);
            });
        }
    }

    async fetchCustomers() {
        try {
            this.uiManager.showLoading(true, this.config.UI.LOADING_DELAY);

            const url = `${this.config.API.BASE_URL}${this.config.API.ENDPOINTS.CUSTOMERS}`;
            const response = await this.httpClient.request(url);

            // Laravel APIレスポンス形式に対応
            const data = response.data || response;

            if (!Array.isArray(data)) {
                throw new ValidationError('Invalid data format: expected array', 'data', data);
            }

            this.customers = this.#processCustomerData(data);
            this.#updateCache();
            this.searchCustomers();

        } catch (error) {
            console.error('Failed to fetch customers:', error);
            this.uiManager.showError(this.#getErrorMessage(error));
        } finally {
            this.uiManager.showLoading(false);
        }
    }

    #processCustomerData(data) {
        return data
            .map(customer => {
                // Laravel形式のデータを変換
                const normalizedCustomer = {
                    id: customer.id,
                    name: customer.name,
                    email: customer.email,
                    phone: customer.phone,
                    status: customer.status || 'active',
                    createdAt: customer.created_at
                };

                const validation = this.validator.validateCustomer(normalizedCustomer);
                if (!validation.isValid) {
                    console.warn('Invalid customer data:', validation.errors);
                    // バリデーションエラーがあっても表示する（警告のみ）
                }
                return this.validator.sanitizeCustomer(normalizedCustomer);
            })
            .filter(Boolean);
    }

    #updateCache() {
        this.customerCache.clear();
        this.customers.forEach(customer => {
            this.customerCache.set(customer.id, customer);
        });
    }

    searchCustomers() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const container = document.getElementById('customerList');

        if (!container) {
            console.error('Customer list container not found');
            return;
        }

        const searchTerm = searchInput ? SecurityUtils.sanitizeInput(searchInput.value.toLowerCase()) : '';
        const statusFilterValue = statusFilter ? statusFilter.value : '';

        const filteredCustomers = this.customers.filter(customer => {
            const nameMatch = !searchTerm ||
                customer.name.toLowerCase().includes(searchTerm) ||
                (customer.email && customer.email.toLowerCase().includes(searchTerm));
            const statusMatch = !statusFilterValue ||
                customer.status === statusFilterValue;
            return nameMatch && statusMatch;
        });

        this.uiManager.renderCustomers(filteredCustomers, container);
    }

    #getErrorMessage(error) {
        if (error instanceof ValidationError) {
            return 'データの形式が正しくありません。';
        }

        if (error instanceof NetworkError) {
            if (error.status >= 500) {
                return 'サーバーエラーが発生しました。しばらくしてからもう一度お試しください。';
            }
            return 'ネットワークエラーが発生しました。接続を確認してください。';
        }

        return '顧客データの取得に失敗しました。しばらくしてからもう一度お試しください。';
    }

    // パブリックメソッド
    getCustomerById(id) {
        return this.customerCache.get(id) || null;
    }

    refresh() {
        return this.fetchCustomers();
    }

    destroy() {
        this.httpClient.cancelAllRequests();
        this.eventListeners.forEach(removeListener => removeListener());
        this.eventListeners = [];
        this.customerCache.clear();
        this.customers = [];
    }
}

// アプリケーション初期化
class CustomerApp {
    static #instance = null;

    constructor() {
        if (CustomerApp.#instance) {
            return CustomerApp.#instance;
        }

        this.customerManager = null;
        this.#init();

        CustomerApp.#instance = this;
    }

    #init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.#startApp());
        } else {
            this.#startApp();
        }

        this.#setupErrorHandling();
        this.#setupCleanup();
    }

    async #startApp() {
        try {
            // 顧客リストページでのみ初期化
            const customerListContainer = document.getElementById('customerList');
            if (customerListContainer) {
                this.customerManager = new CustomerManager();
            }
        } catch (error) {
            console.error('Failed to start application:', error);
        }
    }

    #setupErrorHandling() {
        window.addEventListener('error', (event) => {
            console.error('Uncaught error:', event.error);
        });

        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            event.preventDefault();
        });
    }

    #setupCleanup() {
        const cleanup = () => {
            if (this.customerManager) {
                this.customerManager.destroy();
            }
        };

        window.addEventListener('beforeunload', cleanup);
        window.addEventListener('pagehide', cleanup);
    }

    static getInstance() {
        return new CustomerApp();
    }

    getManager() {
        return this.customerManager;
    }
}

// グローバルに公開
window.CustomerApp = CustomerApp;
window.CustomerManager = CustomerManager;
window.SecurityUtils = SecurityUtils;
window.DOMUtils = DOMUtils;

// 自動初期化（オプション）
if (document.getElementById('customerList')) {
    CustomerApp.getInstance();
}
