# fingerprint_api.py
from flask import Flask, request, jsonify
import hashlib

app = Flask(__name__)

# Example hashed fingerprint to compare with (replace with real one later)
STORED_HASH = "5e884898da28047151d0e56f8dc6292773603d0d6aabbddc6bbfba6f2e5e7ad6"  # hash of "password"

@app.route('/match_fingerprint', methods=['POST'])
def match_fingerprint():
    fingerprint = request.json.get('fingerprint')
    if not fingerprint:
        return jsonify({'error': 'No fingerprint received'}), 400

    hashed_input = hashlib.sha256(fingerprint.encode()).hexdigest()
    match = hashed_input == STORED_HASH

    return jsonify({'matched': match, 'confidence': 100 if match else 0})

if __name__ == '__main__':
    app.run(port=5000)
