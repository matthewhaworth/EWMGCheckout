import React, {Component} from 'react';
import { withScriptjs, withGoogleMap, GoogleMap, Marker } from "react-google-maps"

class StoreList extends Component {
    constructor(props, context) {
        super(props, context);
        this.state = {};
    }

    toggleStoreDetail(storeId, e) {
        e.preventDefault();
        if (this.state.selectedStore === storeId) {
            this.setState({selectedStore: null});
        } else {
            this.setState({selectedStore: storeId});
        }
    }

    getOpeningHoursList(store){

        const dayMap = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        if(!store.opening_hours) { return ''; }

        return (<dl className="cc-map__timetable">{ store.opening_hours.map((openingHour, dayIndex) =>
            <div className="cc-map__timetable-item" key={dayIndex}>
                <div className="cc-map__timetable-day">{dayMap[dayIndex]}:</div>
                <div className="cc-map__timetable-hours">{openingHour}</div>
            </div>)}
        </dl>);
    }


    render() {
        const StoreMapComponent = withScriptjs(withGoogleMap((props) =>
            <GoogleMap
                defaultZoom={12}
                defaultCenter={props.geolocation}>
                {props.isMarkerShown && <Marker position={props.geolocation}/>}
            </GoogleMap>
        ));

        const selectedStore = this.state.selectedStore;

        const storesList = this.props.stores.map((store, i) => {

            return <div key={i} className="form__radio form__radio--address">
                <label className="form__label">
                    <input type="radio" name="delivery"  value={store.id} onClick={() => this.props.onChooseStore(store.id)}/>
                    <div className="form__label-text">
                        <span className="strong">{store.name}</span><br/>
                        {store.distance} miles {i === 0 && <span className="hightlight">NEAREST</span>}<br/>
                        {store.address}
                    </div>
                </label>
                <div className={'cc-map' + (selectedStore === store.id ? ' active' : '')}>
                    <a href="#" className="cc-map__trigger" onClick={(e) => this.toggleStoreDetail(store.id, e)}>View Details</a>
                    {selectedStore === store.id &&
                        <div className="cc-map__content">
                            <div className="cc-map__gmap">
                                <StoreMapComponent isMarkerShown
                                                   geolocation={store.geolocation}
                                                   googleMapURL="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry,drawing,places"
                                                   loadingElement={<div style={{height: '100%'}}/>}
                                                   containerElement={<div style={{height: '150px'}}/>}
                                                   mapElement={<div style={{height: '100%'}}/>}/>
                            </div>
                            {this.getOpeningHoursList(store)}
                        </div>}
                </div>
            </div>
        });

        return (this.props.stores.length > 0 && <div className="checkout-addresslist">
            <div className="checkout-addresslist__container">{storesList}</div>
        </div>);
    }
};

export default StoreList;