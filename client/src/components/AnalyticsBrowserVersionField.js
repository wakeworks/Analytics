import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import i18n from 'i18n';

class AnalyticsBrowserVersionField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            browsers: props.chartData,
            fullCount: Object.values(props.chartData).reduce((prev, curr) => prev + curr, 0)
        }
    }

    getChartOptions() {
        return {
            chart: {
                type: 'bar'
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: Object.keys(this.state.browsers)
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    horizontal: true
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                y: {
                    formatter: (value, opts) => {
                        const percent = (value / this.state.fullCount) * 100;
                        return percent.toFixed(0) + '%'
                    }
                }
            }
        };
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    { !this.state.browsers && <Skeleton count={10} /> }
                    { !!this.state.browsers && <Chart
                        options={this.getChartOptions()}
                        series={[{
                            name: i18n._t('Analytics.BrowserShare', 'Share'),
                            data: Object.values(this.state.browsers)
                        }]}
                        type="bar"
                        width="500"
                        height="400"
                    /> }
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsBrowserVersionField);