import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import fetch from 'isomorphic-fetch';

class AnalyticsOSField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            os: props.chartData
        }
    }

    getChartOptions() {
        return {
            chart: {
                type: 'pie'
            },
            labels: Object.keys(this.state.os)
        };
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    <div class="analytics-chart">
                        { !this.state.os && <Skeleton count={10} /> }
                        { !!this.state.os && <Chart
                            options={this.getChartOptions()}
                            series={Object.values(this.state.os)}
                            type="pie"
                            width="500"
                            height="400"
                        /> }
                    </div>
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsOSField);