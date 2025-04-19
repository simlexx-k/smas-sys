export default {
    install(app) {
        app.config.globalProperties.$filters = {
            currency(value) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(value);
            }
        }
    }
} 