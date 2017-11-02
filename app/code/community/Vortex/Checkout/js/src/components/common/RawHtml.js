import React from 'react'

const RawHtml = ({output, className = ""}) =>{
    return (
        <div className={className} dangerouslySetInnerHTML={{__html: output}} />
    );
}

export default RawHtml