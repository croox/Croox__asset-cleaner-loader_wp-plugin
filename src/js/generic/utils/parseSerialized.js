
const parseSerialized = ( str, fallback = {} ) => {
	let object = fallback;
	try {
		object = JSON.parse( str );
	} catch(e) {
		console.log( e );
		object = object;
	}
	return object;
};

export default parseSerialized;
