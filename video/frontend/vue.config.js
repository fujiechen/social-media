const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  publicPath: process.env.VUE_APP_SUB_PATH,
  transpileDependencies: true,
  css: {
    loaderOptions: {
      // provide global variables
      sass: {
        additionalData: `
          @import "~@/styles/variables.scss";
        `,
      },
    },
  },
})
