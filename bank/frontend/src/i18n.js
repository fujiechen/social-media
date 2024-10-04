import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import cn from './locales/cn.json';
import es from './locales/es.json';
import fr from './locales/fr.json';
import ja from './locales/ja.json';
import pt from './locales/pt.json';

const messages = {
  en,
  cn,
  es,
  fr,
  ja,
  pt,
};

export default createI18n({
  legacy: false,
  locale: process.env.VUE_APP_I18N_LOCALE || 'cn',
  fallbackLocale: process.env.VUE_APP_I18N_FALLBACK_LOCALE || 'cn',
  messages,
});
