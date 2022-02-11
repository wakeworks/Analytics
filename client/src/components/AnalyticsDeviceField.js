import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import fetch from 'isomorphic-fetch';

class AnalyticsDeviceField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            devices: props.chartData
        }
    }

    getChartOptions() {
        return {
            chart: {
                type: 'pie'
            },
            labels: Object.keys(this.state.devices).map(device => `${device.charAt(0).toUpperCase()}${device.substring(1)}`)
        };
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    { !this.state.devices && <Skeleton count={10} /> }
                    { !!this.state.devices && <Chart
                        options={this.getChartOptions()}
                        series={Object.values(this.state.devices)}
                        type="pie"
                        width="500"
                        height="400"
                    /> }
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsDeviceField);