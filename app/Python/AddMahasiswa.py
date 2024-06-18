import cv2
from ultralytics import YOLO
import sys
import face_recognition
import pickle

# Load the face detection model
model = YOLO("/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/yolov8l-face.pt")

# Path to the image
img_path = sys.argv[1]

# Path to the folder containing known faces
known_faces_dir = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces"

#pickle file
known_face_encodings_file = "known_face_encodings.pkl"

# Detect faces in the image
results = model(img_path)
boxes = results[0].boxes

# Read the original image
img = cv2.imread(img_path)

known_face_encodings = []

with open(known_face_encodings_file, 'rb') as f:
    known_face_encodings = pickle.load(f)

# Loop through each detected face
# if len(boxes) <= 1:
for box in boxes:
    # Extract coordinates
    top_left_x = int(box.xyxy.tolist()[0][0])
    top_left_y = int(box.xyxy.tolist()[0][1])
    bottom_right_x = int(box.xyxy.tolist()[0][2])
    bottom_right_y = int(box.xyxy.tolist()[0][3])

    # Crop the face
    cropped_img = img[top_left_y:bottom_right_y, top_left_x:bottom_right_x]

    # Create filename
    filename = f"{known_faces_dir}/{sys.argv[2]}.jpg"

    image_name = f"{sys.argv[2]}.jpg"

    # Save the cropped image
    cv2.imwrite(filename, cropped_img)

    known_face_image = face_recognition.load_image_file(filename)
    known_face_encoding = face_recognition.face_encodings(known_face_image)[0]  # assuming one face per image
    known_face_encodings[image_name] = known_face_encoding

    with open(known_face_encodings_file, 'wb') as f:
        pickle.dump(known_face_encodings, f)

    print(filename)

    print("Face saved successfully!")

# else:
#     print("More than 1 face is detected")
