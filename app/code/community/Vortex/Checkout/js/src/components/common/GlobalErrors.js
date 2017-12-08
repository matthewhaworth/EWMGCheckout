import React, {Component} from 'react';
import {bindActionCreators} from "redux";
import {connect} from "react-redux";
import * as errorActions from "../../actions/errorActions";
import Transition from 'react-transition-group/Transition';

class GlobalErrors extends Component {
    render() {
        const {errorActions} = this.props;
        let allErrors = [];

        Object.keys(this.props.errors).forEach((section) => allErrors = allErrors.concat(this.props.errors[section]));

        const displayErrors = allErrors.length > 0;
        const errorsList = allErrors.map((message, index) => <li key={index}>{message}</li>);


        return (displayErrors) ?
            <Transition in={true} onEntered={setTimeout(() => { errorActions.clearAllErrors() }, 6000)}>
                {(status) => (
                    <div className={'checkout-globalerrors checkout-globalerrors--' + (status)}>
                        <div className="checkout-globalerrors__container">
                            <ul>{errorsList}</ul>
                        </div>
                    </div>
                )}
            </Transition> : null;
    }

}

const mapStateToProps = (state) => {
    return {
        errors: state.errors
    }
};

const mapDispatchToProps = (dispatch) => {
    return {
        errorActions: bindActionCreators(errorActions, dispatch)
    }
};

export default connect(mapStateToProps, mapDispatchToProps)(GlobalErrors);