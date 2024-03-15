const HttpSimpler = require('./js/HttpSimpler.js');

const options = {
	port: 3001,
	hostname: '0.0.0.0',
	file_encoding: 'utf8',
	show_folder: true,
}

const server = HttpSimpler.createServer(options);

server.listen();