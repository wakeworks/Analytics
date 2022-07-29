import Injector from 'lib/Injector';
import AnalyticsHitsField from 'components/AnalyticsHitsField';
import AnalyticsBrowserField from 'components/AnalyticsBrowserField';
import AnalyticsBrowserVersionField from 'components/AnalyticsBrowserVersionField';
import AnalyticsDeviceField from 'components/AnalyticsDeviceField';
import AnalyticsOSField from 'components/AnalyticsOSField';
import AnalyticsPagesField from 'components/AnalyticsPagesField';

const registerComponents = () => {
  Injector.component.register('AnalyticsHitsField', AnalyticsHitsField);
  Injector.component.register('AnalyticsBrowserField', AnalyticsBrowserField);
  Injector.component.register('AnalyticsBrowserVersionField', AnalyticsBrowserVersionField);
  Injector.component.register('AnalyticsDeviceField', AnalyticsDeviceField);
  Injector.component.register('AnalyticsOSField', AnalyticsOSField);
  Injector.component.register('AnalyticsPagesField', AnalyticsPagesField);
};

export default registerComponents;