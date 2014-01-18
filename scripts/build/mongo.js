// Set expiration time

db.hashes.drop();
db.images.drop();

db.hashes.ensureIndex({'timestamp': 1}, {expireAfterSeconds: 60 * 60 * 24 * 3});
db.images.ensureIndex({'timestamp': 1}, {expireAfterSeconds: 60 * 60 * 24 * 3});