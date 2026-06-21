const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

config.resolver.resolverMainFields = ['react-native', 'browser', 'main'];

config.resolver.platforms = ['android', 'ios', 'native', 'web'];

module.exports = config;
