import mysql.connector as mysql
import nltk
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from nltk.stem.snowball import FrenchStemmer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
from flask import Flask, request, jsonify
from gevent import monkey
from flask_cors import CORS
import numpy as np

monkey.patch_all()
app = Flask(__name__)
CORS(app, resources={

    
    r"/api/*": {
        "origins": ["http://localhost", "http://127.0.0.1"],
        "methods": ["GET", "OPTIONS"]
    }
})

# Initialisation NLTK
nltk.download('stopwords', quiet=True)
nltk.download('punkt', quiet=True)

def get_db_connection():
    return mysql.connect(
        host='localhost',
        user='root',
        password='',
        database='eventini'
    )

def preprocess_text(text):
    stemmer = FrenchStemmer()
    stopw = set(stopwords.words('french'))
    tokens = word_tokenize(text.lower())
    filtered_words = [stemmer.stem(word) for word in tokens if word not in stopw and word.isalpha()]
    return ' '.join(filtered_words)

def load_event_data():
    try:
        conx = get_db_connection()
        cursor = conx.cursor(dictionary=True)
        cursor.execute('SELECT evenement_id, nom, description FROM evenement')
        events = cursor.fetchall()
        return {
            event['evenement_id']: {
                'nom': event['nom'],
                'desc': event['description'],
                'processed': preprocess_text(f"{event['nom']} {event['description']}")
            } for event in events
        }
    finally:
        cursor.close()
        conx.close()

# Initialisation au démarrage
try:
    events_data = load_event_data()
    vectorizer = TfidfVectorizer()
    tfidf_matrix = vectorizer.fit_transform([e['processed'] for e in events_data.values()])
except Exception as e:
    print(f"Erreur d'initialisation: {str(e)}")
    events_data = {}
    tfidf_matrix = None

def search_events(query, k=5):
    if not query or not events_data:
        return []
    
    processed_query = preprocess_text(query)
    try:
        query_vector = vectorizer.transform([processed_query])
        similarities = cosine_similarity(query_vector, tfidf_matrix).flatten()
        ranked_indices = np.argsort(similarities)[::-1][:k]
        return [
            {
                'id': list(events_data.keys())[idx],
                'nom': events_data[list(events_data.keys())[idx]]['nom'],
                'score': float(similarities[idx])
            }
            for idx in ranked_indices if similarities[idx] > 0.1
        ]
    except Exception as e:
        print(f"Erreur de recherche: {str(e)}")
        return []

@app.route('/api/search', methods=['GET'])
def handle_search():
    query = request.args.get('q', '')
    return jsonify(search_events(query))

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)