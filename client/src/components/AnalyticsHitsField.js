import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import fetch from 'isomorphic-fetch';

class AnalyticsHitsField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            ...this.populateChartData(props.chartData)
        };
    }

    getChartOptions() {
        return {
            chart: {
                id: 'area-datetime',
                type: 'area',
                zoom: {
                    autoScaleYaxis: true
                }
            },
            dataLabels: {
                enabled: false
            },
            markers: {
                size: 0,
                style: 'hollow',
            },
            xaxis: {
                type: 'datetime',
                tickAmount: 1,
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy'
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 100]
                }
            },
        };
    }

    fillDays(days, from, to) {
        let filled = { ...days };
        let currentDay = new Date(from);
        let maxDay = new Date(to);
        while (currentDay.getTime() < maxDay.getTime()) {
            let key = `${currentDay.getFullYear().toString().padStart(2, '0')}-${(currentDay.getMonth() + 1).toString().padStart(2, '0')}-${currentDay.getDate().toString().padStart(2, '0')}`;
            if (filled[key] === undefined) {
                filled[key] = 0;
            }
            currentDay.setDate(currentDay.getDate() + 1);
        }

        return filled;
    }

    populateChartData(data) {
        const filledHits = this.fillDays(data['Hits']['Days'], data['Hits']['Start'], data['Hits']['End']);
        const filledUnique = this.fillDays(data['Unique']['Days'], data['Unique']['Start'], data['Unique']['End']);

        return {
            hitsSeries: Object.entries(filledHits).sort((a, b) => a[0] < b[0]),
            uniqueSeries: Object.entries(filledUnique).sort((a, b) => a[0] < b[0])
        }
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    { !this.state.hitsSeries || !this.state.uniqueSeries && <Skeleton count={10} /> }
                    { !!this.state.hitsSeries && !!this.state.uniqueSeries && <Chart
                        options={this.getChartOptions()}
                        series={[{
                            name: 'Daily hits',
                            data: this.state.hitsSeries
                        }, {
                            name: 'Unique visits',
                            data: this.state.uniqueSeries
                        }]}
                        width="500"
                        height="400"
                    /> }
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsHitsField);