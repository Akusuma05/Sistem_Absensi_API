import sys
import pickle

# Pickle file
known_face_encodings_file = "known_face_encodings.pkl"

# Load existing face encodings from the pickle file
known_face_encodings = {}

image_name = f"{sys.argv[1]}.jpg"

with open(known_face_encodings_file, 'rb') as f:
    known_face_encodings = pickle.load(f)

if image_name in known_face_encodings:
    # Remove the face encoding entry
    del known_face_encodings[image_name]

    # Save the updated face encodings back to the pickle file
    with open(known_face_encodings_file, 'wb') as f:
        pickle.dump(known_face_encodings, f)

    print(f"Face with filename {image_name} deleted successfully.")
else:
    print(f"Face with filename {image_name} not found in the dictionary. No action taken.")
