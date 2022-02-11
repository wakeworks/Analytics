import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import fetch from 'isomorphic-fetch';

class AnalyticsUrlsField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            urls: props.chartData
        }
    }

    getChartOptions() {
        return {
            chart: {
                type: 'bar'
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: {
                    colors: ['#fff']
                },
                formatter: function (val, opt) {
                    return opt.w.globals.labels[opt.dataPointIndex]
                },
                offsetX: 0,
                dropShadow: {
                    enabled: true
                }
            },
            xaxis: {
                categories: Object.keys(this.state.urls)
            },
            yaxis: {
                labels: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    horizontal: true,
                    dataLabels: {
                        position: 'bottom'
                    }
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
                    {!this.state.urls && <Skeleton count={10} />}
                    {!!this.state.urls && <Chart
                        options={this.getChartOptions()}
                        series={[{
                            name: 'URLs',
                            data: Object.values(this.state.urls).map((val) => val.Count)
                        }]}
                        type="bar"
                        width="500"
                        height="400"
                    />}
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsUrlsField);