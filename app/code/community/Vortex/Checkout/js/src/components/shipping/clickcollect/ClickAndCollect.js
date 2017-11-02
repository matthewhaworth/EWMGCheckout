import React, {Component} from 'react';
import ClickCollectForm from "./ClickCollectForm";
import PostcodeApi from "../../../api/PostcodeApi";
import StoreList from "./StoreList";
import { withScriptjs, withGoogleMap, GoogleMap, Marker } from "react-google-maps"

class ClickAndCollect extends Component {
    
    constructor(props, context) {
        super(props, context);

        this.state = { postcode: '', stores: [], loading: false, changingStore: false };
    }

    onUpdatePostcode(event) {
        this.setState({ postcode: event.target.value });
    }
    
    onSubmitPostcode() {
        this.setState({loading: true});
        PostcodeApi.geocode( this.state.postcode ).then((response) => {
            const latitude = response['Items'].length > 0 ? response['Items'][0]['Latitude'] : 'notfound';
            const longitude = response['Items'].length > 0 ? response['Items'][0]['Longitude'] : 'notfound';
            this.props.listClickAndCollectStores(latitude, longitude).then((response) => {
                this.setState({ stores: response, loading: false });
            }).catch(() => {
                this.setState({ loading: false });
            });
        });
    }

    enableLookup() {
        this.setState({changingStore: true});
        this.props.clearClickAndCollectStore().then(() => {
            this.setState({changingStore: false});
        }).catch(() => {
            this.setState({changingStore: false});
        });
    }

    renderDetail() {
        const store = this.props.currentClickAndCollectStore;

        const StoreMapComponent = withScriptjs(withGoogleMap((props) =>
            <GoogleMap
                defaultZoom={12}
                defaultCenter={store.geolocation}>
                {props.isMarkerShown && <Marker position={store.geolocation}/>}
            </GoogleMap>
        ));

        const dayMap = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        const openingHoursList = store.opening_hours.map((openingHour, dayIndex) =>
            <div className="cc-map__timetable-item" key={dayIndex}>
                <div className="cc-map__timetable-day">{dayMap[dayIndex]}:</div>
                <div className="cc-map__timetable-hours">{openingHour}</div>
            </div>
        );

        return <div className="checkout-addresslist checkout-addresslist--result">
            <div className="checkout-addresslist__container">
                <div className="checkout-addresslist__label">
                    <span className="strong">{store.name}</span><br/>
                    {store.address}
                </div>

                <div className="cc-map active">
                    <div className="cc-map__content">
                        <div className="cc-map__gmap">
                            <StoreMapComponent isMarkerShown
                                               geolocation={store.geolocation}
                                               googleMapURL="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry,drawing,places"
                                               loadingElement={<div style={{height: '100%'}}/>}
                                               containerElement={<div style={{height: '150px'}}/>}
                                               mapElement={<div style={{height: '100%'}}/>}/>
                        </div>
                        {(store.opening_hours && store.opening_hours.length > 0) && <div className="cc-map__timetable">
                            {openingHoursList}
                        </div>}

                    </div>
                </div>

            </div>

            <div className="form__control form__control--actions">
                <button type='button'
                        onClick={() => this.enableLookup()}
                        className={'button button--primary ' + (this.state.changingStore ? 'button--loading' : 'button--arrow-right')}>
                    <span>Change store</span>
                </button>
            </div>
        </div>
    }

    renderLookup() {
        return <div>
            <ClickCollectForm
                postcode={this.state.postcode}
                loading={this.state.loading}
                onUpdatePostcode={(e) => this.onUpdatePostcode(e)}
                onSubmitPostcode={(e) => this.onSubmitPostcode(e)} />

            <StoreList stores={this.state.stores} onChooseStore={this.props.setClickAndCollectStore} />
        </div>
    }

    render() {
        return this.props.isClickAndCollectChosen ? this.renderDetail() : this.renderLookup();
    }
}

export default ClickAndCollect;