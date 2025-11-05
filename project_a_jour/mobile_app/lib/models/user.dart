class User {
  final int id;
  final String email;
  final String firstName;
  final String lastName;
  final List<String> roles;
  final DateTime createdAt;

  User({
    required this.id,
    required this.email,
    required this.firstName,
    required this.lastName,
    required this.roles,
    required this.createdAt,
  });

  String get fullName => '$firstName $lastName';

  bool get isAdmin => roles.contains('ROLE_ADMIN');

  factory User.fromJson(Map<String, dynamic> json) {
    print('User.fromJson incoming: ' + json.toString());
    return User(
      id: json['id'],
      email: json['email'] ?? '',
      firstName: json['firstName'] ?? '',
      lastName: json['lastName'] ?? '',
      roles: (json['roles'] != null) ? List<String>.from(json['roles']) : ['ROLE_USER'],
      createdAt: (json['createdAt'] != null && json['createdAt'] != '')
        ? DateTime.tryParse(json['createdAt']) ?? DateTime.now()
        : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'email': email,
      'firstName': firstName,
      'lastName': lastName,
      'roles': roles,
      'createdAt': createdAt.toIso8601String(),
    };
  }
}