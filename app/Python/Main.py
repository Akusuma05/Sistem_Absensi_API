import face_recognition
import cv2
from ultralytics import YOLO
import os
import sys
import pickle

# Load the face detection model
model = YOLO("/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/yolov8l-face.pt")

# Path to the image
img_path = sys.argv[1]

# Path to the folder containing known faces
known_faces_dir = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces"

# Path to the folder where cropped faces will be saved (create it if it doesn't exist)
cropped_face_folder = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/cropped_faces"
os.makedirs(cropped_face_folder, exist_ok=True)

# Load or compute known face encodings
known_face_encodings_file = "known_face_encodings.pkl"

if os.path.exists(known_face_encodings_file):
    with open(known_face_encodings_file, 'rb') as f:
        known_face_encodings = pickle.load(f)
else:
    known_face_encodings = {}
    for filename in os.listdir(known_faces_dir):
        if filename.lower().endswith(('.jpg', '.jpeg', '.png')):
            known_face_image = face_recognition.load_image_file(os.path.join(known_faces_dir, filename))
            known_face_encoding = face_recognition.face_encodings(known_face_image)[0]  # assuming one face per image
            known_face_encodings[filename] = known_face_encoding

    with open(known_face_encodings_file, 'wb') as f:
        pickle.dump(known_face_encodings, f)

# Detect faces in the image
results = model(img_path)
boxes = results[0].boxes

# Read the original image
img = cv2.imread(img_path)

# Loop through each detected face
for i, box in enumerate(boxes):
    top_left_x, top_left_y, bottom_right_x, bottom_right_y = map(int, box.xyxy[0])

    # Crop the face
    cropped_img = img[top_left_y:bottom_right_y, top_left_x:bottom_right_x]

    # Create filename based on image count
    filename = f"{cropped_face_folder}/cropped_face_{i}.jpg"

    # Save the cropped image
    cv2.imwrite(filename, cropped_img)

    # Load cropped face image for comparison
    cropped_face_image = face_recognition.load_image_file(filename)
    cropped_face_encodings = face_recognition.face_encodings(cropped_face_image)

    if len(cropped_face_encodings) > 0:
        cropped_face_encoding = cropped_face_encodings[0]

        # Compare the face encoding with known faces and find the highest accuracy match
        best_match = None
        best_match_accuracy = 0

        for known_filename, known_encoding in known_face_encodings.items():
            match = face_recognition.compare_faces([known_encoding], cropped_face_encoding, tolerance=0.5)[0]
            if match:
                face_distance = face_recognition.face_distance([known_encoding], cropped_face_encoding)
                accuracy = 1 - face_distance[0]
                if accuracy > best_match_accuracy:
                    best_match = known_filename
                    best_match_accuracy = accuracy

        if best_match:
            print(f"Match found for {filename} vs. known face {best_match} with accuracy: {best_match_accuracy:.2f}%")
        else:
            print(f"No faces found in {filename}. Skipping comparison")
    else:
        print(f"No face encoding found for {filename}. Skipping comparison")

# Delete all cropped face images
cropped_face_filenames = os.listdir(cropped_face_folder)
for file_name in cropped_face_filenames:
    file_path = os.path.join(cropped_face_folder, file_name)
    if os.path.isfile(file_path):
        os.remove(file_path)

print("Cropped faces processed successfully!")
