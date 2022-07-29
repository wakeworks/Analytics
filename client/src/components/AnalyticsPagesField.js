import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'
import i18n from 'i18n';

class AnalyticsPagesField extends Component {
    constructor(props) {
        super(props);

        this.state = {
            urls: props.chartData.URLs,
            pages: props.chartData.Pages,
            type: localStorage.getItem('AnalyticsPagesField.type') || 'pages'
        }

        // Bind this to all functions because React
        // ...
        this.handleTypeChange = this.handleTypeChange.bind(this);
    }

    getChartOptions() {
        return {
            chart: {
                type: 'bar'
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'center',
                style: {
                    colors: ['#fff']
                },
                formatter: function (val, opt) {
                    return opt.w.globals.labels[opt.dataPointIndex]
                },
                offsetX: 10,
                background: {
                    enabled: true,
                    foreColor: '#005a93',
                    borderWidth: 0,
                    opacity: 0.5
                }
            },
            xaxis: {
                categories: this.getCategories()
            },
            yaxis: {
                labels: {
                    show: false,
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

    getCategories() {
        if(this.state.type === 'pages') {
            return this.state.pages.map((val) => val.Title);
        } else {
            return this.state.urls.map((val) => val.URL);
        }
    }

    handleTypeChange(event) {
        this.setState({
            type: event.target.value
        });

        localStorage.setItem('AnalyticsPagesField.type', event.target.value);
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    <div class="choose-box">
                        <div class="choose-box__wrapper">
                            {
                                [['pages', i18n._t('Analytics.Pages', 'Pages')], ['urls', i18n._t('Analytics.URLs', 'URLs')]].map((entry) => {
                                    return (
                                        <div className={"choose-box__field " + (this.state.type == entry[0] ? "active" : "") }>
                                            <input type="radio" id={"type-" + entry[0]} name="type" value={entry[0]} onChange={this.handleTypeChange} />
                                            <label for={"type-" + entry[0]}>{entry[1]}</label>
                                        </div>
                                    )
                                })
                            }
                        </div>
                    </div>

                    {!!this.state[this.state.type] && <Chart
                        options={this.getChartOptions()}
                        series={[{
                            name: i18n._t('Analytics.Hits', 'Hits'),
                            data: Object.values(this.state[this.state.type]).map((val) => val.Count)
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

export default fieldHolder(AnalyticsPagesField);