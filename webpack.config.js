const Encore = require("@symfony/webpack-encore")

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (! Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev")
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath("public/build/")

    // public path used by the web server to access the output path
    .setPublicPath("/build")

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry("app", "./resources/js/app.js")
    .addEntry("dashboard", "./resources/js/dashboard.js")
    .addEntry("clients", "./resources/js/clients.js")
    .addEntry("jobs", "./resources/js/jobs.js")
    .addEntry("auth", "./resources/js/auth.js")
    .addEntry("jobtype", "./resources/js/jobtype.js")
    .addEntry("paymethod", "./resources/js/paymethod.js")
    .addEntry("department", "./resources/js/department.js")
    .addEntry("settings", "./resources/js/settings.js")
    .addEntry("users", "./resources/js/users.js")
    .addEntry("payments", "./resources/js/payments.js")
    .addEntry("paystatus", "./resources/js/paystatus.js")
    .addEntry("expenses", "./resources/js/expenses.js")
    .addEntry("category", "./resources/js/category.js")
    .addEntry("sponsor", "./resources/js/sponsor.js")
    .addEntry("jobtype_reports", "./resources/js/jobtype_reports.js")
    .addEntry("expense_reports", "./resources/js/expense_reports.js")
    .addEntry("profit_loss", "./resources/js/profit_loss.js")
    .addEntry("yearly_reports", "./resources/js/yearly_reports.js")

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(! Encore.isProduction())

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    .configureBabel((config) => {
        config.plugins.push("@babel/plugin-proposal-class-properties")
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = "usage"
        config.corejs      = 3
    })

    .copyFiles({
        from: "./resources/images",
        to: "images/[path][name].[hash:8].[ext]",
        pattern: /\.(png|jpg|jpeg|gif)$/
    })

    // enables Sass/SCSS support
    .enableSassLoader()

module.exports = Encore.getWebpackConfig()