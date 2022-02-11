import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import fetch from 'isomorphic-fetch';

class AnalyticsBrowserVersionField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            browsers: props.chartData
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
                            name: 'Browsers',
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